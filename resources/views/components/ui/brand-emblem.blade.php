@php
    // Every id inside the SVG has to be unique per instance: the homepage
    // renders this twice, and duplicate ids would leave the second copy
    // referencing the first copy's gradients.
    $uid = uniqid('emblem-');
@endphp

{{-- ===================================================================
     Brand emblem
     ===================================================================
     Stands in for photography. The office identity is architectural — the
     wordmark's mark is a Najdi pointed arch with a latticed window — so the
     substitute is the same building drawn at elevation rather than a stock
     gavel, scales or courthouse, which is exactly the generic legal look the
     identity rules out.

     Drawn rather than photographed, so it costs about 2 KB inline, needs no
     network request, and scales to any frame without a srcset.

     Composed for an ink surface: the openings are cut darker than the mass
     and the linework is gold, so it reads as a lit facade at night. On a
     light surface the fills would invert and it would flatten.
     =================================================================== --}}
<svg viewBox="0 0 400 520"
     fill="none"
     xmlns="http://www.w3.org/2000/svg"
     role="presentation"
     aria-hidden="true"
     {{ $attributes->merge(['class' => 'block h-full w-full']) }}>

    <defs>
        {{-- A wash behind the facade so the silhouette separates from the
             ink background instead of dissolving into it. --}}
        <radialGradient id="{{ $uid }}-glow" cx="50%" cy="44%" r="60%">
            <stop offset="0%" stop-color="var(--color-ink-600)" stop-opacity="0.9" />
            <stop offset="100%" stop-color="var(--color-ink-600)" stop-opacity="0" />
        </radialGradient>

        {{-- The mass is lit from above: strongest at the parapet, fading out
             before the ground line so the building has no hard bottom edge. --}}
        <linearGradient id="{{ $uid }}-mass" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="var(--color-ink-600)" stop-opacity="0.62" />
            <stop offset="100%" stop-color="var(--color-ink-700)" stop-opacity="0.06" />
        </linearGradient>

        {{-- The lattice is drawn across a rectangle and cut to the arch, which
             keeps the grid true rather than bending it to the opening. --}}
        <clipPath id="{{ $uid }}-arch">
            <path d="M158 470 V330 Q158 262 200 236 Q242 262 242 330 V470 Z" />
        </clipPath>
    </defs>

    <rect x="0" y="60" width="400" height="440" fill="url(#{{ $uid }}-glow)" />

    {{-- --- The mass -------------------------------------------------- --}}
    <path d="M64 496 V176 H336 V496 Z" fill="url(#{{ $uid }}-mass)" />

    {{-- --- Parapet ---------------------------------------------------
         The stepped triangular crown of Najdi mud-brick building, kept as
         outline only so it reads as drawing and not as ornament. --}}
    <g stroke="var(--color-gold)" stroke-width="1" stroke-opacity="0.55" stroke-linejoin="round">
        @for ($i = 0; $i < 8; $i++)
            @php $x = 64 + $i * 34; @endphp
            <path d="M{{ $x }} 176 L{{ $x + 17 }} 152 L{{ $x + 34 }} 176" />
        @endfor
    </g>

    {{-- The mass is a gradient with no edges of its own; without these three
         hairlines it reads as a rectangle of fill that stops, rather than as
         a wall seen straight on. --}}
    <g stroke="var(--color-gold)" stroke-width="1">
        <path d="M64 176 H336" stroke-opacity="0.4" />
        <path d="M64 176 V496" stroke-opacity="0.22" />
        <path d="M336 176 V496" stroke-opacity="0.22" />
    </g>

    {{-- --- The central opening ---------------------------------------
         Two quadratics meeting at a cusp: a true pointed arch, not a
         semicircle with a peak drawn on top. --}}
    <path d="M158 470 V330 Q158 262 200 236 Q242 262 242 330 V470 Z"
          fill="var(--color-ink-950)"
          fill-opacity="0.55" />

    <g clip-path="url(#{{ $uid }}-arch)"
       stroke="var(--color-gold)"
       stroke-width="1"
       stroke-opacity="0.45">
        <path d="M186 236 V470 M214 236 V470" />
        <path d="M158 300 H242 M158 350 H242 M158 400 H242 M158 450 H242" />
    </g>

    <path d="M158 470 V330 Q158 262 200 236 Q242 262 242 330 V470"
          stroke="var(--color-gold)"
          stroke-width="1.5"
          stroke-opacity="0.95" />

    {{-- The recessed second arch, offset inward. It is what gives the wall
         thickness — a single outline would read as a sticker. --}}
    <path d="M172 470 V336 Q172 276 200 254 Q228 276 228 336 V470"
          stroke="var(--color-gold)"
          stroke-width="1"
          stroke-opacity="0.35" />

    {{-- --- The flanking slits ---------------------------------------- --}}
    <g fill="var(--color-ink-950)" fill-opacity="0.45">
        <path d="M96 470 V400 Q96 378 111 366 Q126 378 126 400 V470 Z" />
        <path d="M274 470 V400 Q274 378 289 366 Q304 378 304 400 V470 Z" />
    </g>

    <g stroke="var(--color-gold)" stroke-width="1" stroke-opacity="0.7">
        <path d="M96 470 V400 Q96 378 111 366 Q126 378 126 400 V470" />
        <path d="M274 470 V400 Q274 378 289 366 Q304 378 304 400 V470" />
    </g>

    {{-- --- Ground ----------------------------------------------------
         Two rules rather than one: the building stands on a line, and the
         second, shorter and fainter, reads as its shadow. --}}
    <g stroke="var(--color-gold)">
        <path d="M24 496 H376" stroke-width="1" stroke-opacity="0.6" />
        <path d="M64 504 H336" stroke-width="1" stroke-opacity="0.22" />
    </g>

    {{-- --- The seal ---------------------------------------------------
         A hairline broken by a small lozenge, directly above the apex. The
         same device the pages use between a claim and its explanation. --}}
    <g stroke="var(--color-gold)" stroke-width="1" stroke-opacity="0.4">
        <path d="M24 112 H186" />
        <path d="M214 112 H376" />
    </g>
    <path d="M200 104 L208 112 L200 120 L192 112 Z"
          fill="var(--color-gold)"
          fill-opacity="0.75" />
</svg>
