<?php

namespace App\Support;

use App\Models\Inquiry;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

/**
 * Sends free browser push notifications (Web Push) to admin devices. No SMS gateway or paid service involved.
 */
class PushNotifier
{
    public function isConfigured(): bool
    {
        return filled(config('fdh.vapid.public_key')) && filled(config('fdh.vapid.private_key'));
    }

    /**
     * Alert every active admin who may see inquiries.
     */
    public function newInquiry(Inquiry $inquiry): int
    {
        $users = User::query()->where('is_active', true)->with('roles')->get()
            ->filter(fn (User $user): bool => $user->canAccessAdmin() && $user->can('inquiries.view'));

        return $this->sendToUsers($users, [
            'title' => 'New inquiry: '.$inquiry->name,
            'body' => collect([$inquiry->service, $inquiry->location, $inquiry->phone])->filter()->implode(' · '),
            'url' => route('admin.inquiries', ['open' => $inquiry->id]),
            'tag' => 'inquiry-'.$inquiry->id,
        ]);
    }

    /**
     * @param  array{title: string, body: string, url: string, tag?: string}  $message
     */
    public function sendToUser(User $user, array $message): int
    {
        return $this->sendToUsers(collect([$user]), $message);
    }

    /**
     * Returns how many devices accepted the notification. Subscriptions the browser has revoked are deleted.
     *
     * @param  Collection<int, User>  $users
     * @param  array{title: string, body: string, url: string, tag?: string}  $message
     */
    private function sendToUsers(Collection $users, array $message): int
    {
        if (! $this->isConfigured() || $users->isEmpty()) {
            return 0;
        }

        $subscriptions = PushSubscription::query()->whereIn('user_id', $users->pluck('id'))->get();
        if ($subscriptions->isEmpty()) {
            return 0;
        }

        try {
            $webPush = new WebPush(['VAPID' => [
                'subject' => config('fdh.vapid.subject'),
                'publicKey' => config('fdh.vapid.public_key'),
                'privateKey' => config('fdh.vapid.private_key'),
            ]], ['TTL' => 3600, 'urgency' => 'high']);

            $byHash = [];
            foreach ($subscriptions as $subscription) {
                $byHash[$subscription->endpoint_hash] = $subscription;
                $webPush->queueNotification(Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->public_key,
                    'authToken' => $subscription->auth_token,
                    'contentEncoding' => $subscription->content_encoding,
                ]), json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }

            $delivered = 0;
            foreach ($webPush->flush() as $report) {
                $subscription = $byHash[PushSubscription::hashEndpoint($report->getEndpoint())] ?? null;

                if ($report->isSuccess()) {
                    $delivered++;
                    $subscription?->forceFill(['last_used_at' => now()])->save();
                } elseif ($report->isSubscriptionExpired()) {
                    $subscription?->delete();
                } else {
                    Log::warning('Push notification failed: '.$report->getReason());
                }
            }

            return $delivered;
        } catch (Throwable $e) {
            Log::warning('Push notifications could not be sent: '.$e->getMessage());

            return 0;
        }
    }
}
