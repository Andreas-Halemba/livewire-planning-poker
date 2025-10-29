<svg width="200" height="130" viewBox="0 0 200 130" fill="none" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#6366F1;stop-opacity:1"></stop>
            <stop offset="100%" style="stop-color:#8B5CF6;stop-opacity:1"></stop>
        </linearGradient>
        <linearGradient id="grad2" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#10B981;stop-opacity:1"></stop>
            <stop offset="100%" style="stop-color:#06B6D4;stop-opacity:1"></stop>
        </linearGradient>
        <linearGradient id="grad3" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#F59E0B;stop-opacity:1"></stop>
            <stop offset="100%" style="stop-color:#EF4444;stop-opacity:1"></stop>
        </linearGradient>
        <filter id="glow">
            <feGaussianBlur stdDeviation="2" result="coloredBlur"></feGaussianBlur>
            <feMerge>
                <feMergeNode in="coloredBlur"></feMergeNode>
                <feMergeNode in="SourceGraphic"></feMergeNode>
            </feMerge>
        </filter>
    </defs>

    <!-- Sparkles/Stars -->
    <circle cx="50" cy="25" r="2" fill="#F59E0B" opacity="0.8"></circle>
    <circle cx="150" cy="30" r="1.5" fill="#06B6D4" opacity="0.8"></circle>
    <path d="M 160 20 L 161 22 L 163 23 L 161 24 L 160 26 L 159 24 L 157 23 L 159 22 Z" fill="#8B5CF6" opacity="0.8">
    </path>
    <path d="M 45 18 L 46 20 L 48 21 L 46 22 L 45 24 L 44 22 L 42 21 L 44 20 Z" fill="#10B981" opacity="0.8"></path>

    <!-- Cards with gradients -->
    <g filter="url(#glow)">
        <!-- Card 3 (back) - Orange/Red -->
        <rect x="85" y="20" width="50" height="70" rx="8" fill="url(#grad3)" transform="rotate(10 110 55)"
            opacity="0.9"></rect>
        <text x="110" y="65" font-family="Arial, sans-serif" font-size="32" font-weight="bold" fill="white"
            text-anchor="middle" transform="rotate(10 110 65)">?</text>

        <!-- Card 2 (middle) - Green/Cyan -->
        <rect x="75" y="20" width="50" height="70" rx="8" fill="url(#grad2)"></rect>
        <text x="100" y="65" font-family="Arial, sans-serif" font-size="32" font-weight="bold" fill="white"
            text-anchor="middle">5</text>
        <circle cx="85" cy="30" r="3" fill="white" opacity="0.3"></circle>
        <circle cx="115" cy="80" r="3" fill="white" opacity="0.3"></circle>

        <!-- Card 1 (front) - Blue/Purple -->
        <rect x="65" y="20" width="50" height="70" rx="8" fill="url(#grad1)" transform="rotate(-10 90 55)"></rect>
        <text x="90" y="65" font-family="Arial, sans-serif" font-size="32" font-weight="bold" fill="white"
            text-anchor="middle" transform="rotate(-10 90 65)">8</text>
        <circle cx="75" cy="30" r="3" fill="white" opacity="0.3" transform="rotate(-10 90 55)"></circle>
        <circle cx="105" cy="80" r="3" fill="white" opacity="0.3" transform="rotate(-10 90 55)"></circle>
    </g>

    <!-- Text with gradient -->
    <defs>
        <linearGradient id="textGrad" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color:#6366F1;stop-opacity:1"></stop>
            <stop offset="50%" style="stop-color:#8B5CF6;stop-opacity:1"></stop>
            <stop offset="100%" style="stop-color:#06B6D4;stop-opacity:1"></stop>
        </linearGradient>
    </defs>
    <text x="100" y="115" font-family="Arial, sans-serif" font-size="18" font-weight="800" fill="url(#textGrad)"
        text-anchor="middle">SCRUM POKER</text>
</svg>
