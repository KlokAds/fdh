{{-- Fallback hero artwork: a front-elevation drawing sheet, shown until a real photo is uploaded in the admin. --}}
<svg viewBox="0 0 1200 520" class="elevation" role="img" aria-label="{{ __('Front elevation drawing of a three-storey house') }}" preserveAspectRatio="xMidYMid slice">
    <defs>
        <pattern id="elev-grid" width="40" height="40" patternUnits="userSpaceOnUse">
            <path d="M40 0H0V40" class="e-grid"/>
        </pattern>
        <pattern id="elev-hatch" width="8" height="8" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
            <path d="M0 0V8" class="e-hatch"/>
        </pattern>
    </defs>
    <rect width="1200" height="520" fill="url(#elev-grid)"/>

    {{-- Ground --}}
    <path d="M140 440H1060" class="e-strong"/>
    <rect x="140" y="440" width="920" height="16" fill="url(#elev-hatch)"/>

    {{-- Building mass --}}
    <path d="M360 440V150H840V440" class="e-strong"/>
    <path d="M340 150H860V136H340Z" class="e-line"/>
    <path d="M360 246H840M360 342H840" class="e-line"/>
    <path d="M840 246H900V440" class="e-line"/>

    {{-- Windows --}}
    @foreach ([168, 264] as $y)
        @foreach ([400, 520, 640, 740] as $x)
            <rect x="{{ $x }}" y="{{ $y }}" width="64" height="56" class="e-line"/>
            <path d="M{{ $x + 32 }} {{ $y }}V{{ $y + 56 }}" class="e-thin"/>
        @endforeach
    @endforeach
    <rect x="400" y="360" width="64" height="56" class="e-line"/>
    <rect x="740" y="360" width="64" height="56" class="e-line"/>
    <path d="M560 440V364H640V440" class="e-strong"/>
    <path d="M600 364V440" class="e-thin"/>

    {{-- Balcony rails --}}
    <path d="M500 246V230H720V246M520 230V246M560 230V246M600 230V246M640 230V246M680 230V246" class="e-thin"/>

    {{-- Dimension lines --}}
    <path d="M300 150V440M292 150H308M292 246H308M292 342H308M292 440H308" class="e-dim"/>
    <text x="284" y="202" class="e-text" text-anchor="end">3000</text>
    <text x="284" y="298" class="e-text" text-anchor="end">3000</text>
    <text x="284" y="394" class="e-text" text-anchor="end">3000</text>
    <path d="M360 480H840M360 472V488M840 472V488" class="e-dim"/>
    <text x="600" y="502" class="e-text" text-anchor="middle">15000</text>

    {{-- Title block --}}
    <path d="M940 60H1100V120H940ZM940 84H1100" class="e-line"/>
    <text x="952" y="77" class="e-text">FRONT ELEVATION</text>
    <text x="952" y="105" class="e-text e-muted">SCALE 1:100 · FDH</text>
</svg>
