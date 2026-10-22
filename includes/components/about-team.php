<?php
$teamProfiles = [
    [
        'name' => 'Amir Mirza',
        'role' => 'Managing Director & CEO',
        'image' => '/assets/images/amir-mirza.webp',
        'alt' => 'Amir Mirza, Managing Director and CEO',
        'credentials' => 'ACA (Ireland) | ICAEW | CPA',
        'biography' => 'Amir has over 15 years of professional experience in accounting, taxation and business consultancy, providing practical financial guidance and tailored solutions to individuals, SMEs and corporate clients.',
        'highlights' => [
            ['title' => 'ACA', 'description' => 'Chartered Accountant (ACA Ireland)'],
            ['title' => 'ICAEW', 'description' => 'Institute of Chartered Accountants in England and Wales'],
            ['title' => 'CPA', 'description' => 'Certified Public Accountant'],
            ['title' => '15+ Years', 'description' => 'Professional Experience'],
        ],
    ],
    [
        'name' => 'Dr. Waqar Khan',
        'role' => 'Healthcare & Business Advisor',
        'image' => '/assets/images/waqar-khan.webp',
        'alt' => 'Dr. Waqar Khan',
        'credentials' => 'Healthcare | Business Advisory',
        'biography' => 'Waqar supports healthcare professionals and medical businesses with practical business guidance, planning and operational insight.',
        'highlights' => [
            ['title' => 'Healthcare', 'description' => 'Sector-focused guidance'],
            ['title' => 'Planning', 'description' => 'Practical business planning'],
            ['title' => 'Advisory', 'description' => 'Clear, tailored support'],
            ['title' => 'Experience', 'description' => 'Healthcare and business expertise'],
        ],
    ],
    [
        'name' => 'Dr. Tahir Yasin Khan',
        'role' => 'Business Growth Advisor',
        'image' => '/assets/images/tahir-yasin-khan.webp',
        'alt' => 'Dr. Tahir Yasin Khan',
        'credentials' => 'Business Advisory | Growth',
        'biography' => 'Tahir works with business owners to assess opportunities, strengthen commercial plans and take practical steps towards sustainable growth.',
        'highlights' => [
            ['title' => 'Strategy', 'description' => 'Commercial planning'],
            ['title' => 'Growth', 'description' => 'Opportunity assessment'],
            ['title' => 'Partnership', 'description' => 'Long-term business support'],
            ['title' => 'Experience', 'description' => 'Business growth advisory'],
        ],
    ],
    [
        'name' => 'Dr. Sohaib Adil',
        'role' => 'Technology & Digital Solutions Advisor',
        'image' => '/assets/images/sohaib-adil.webp',
        'alt' => 'Dr. Sohaib Adil',
        'credentials' => 'IT Solutions | Digital Transformation',
        'biography' => 'Sohaib helps organisations explore technology solutions, digital transformation and automation that support the way they work.',
        'highlights' => [
            ['title' => 'Technology', 'description' => 'Practical IT solutions'],
            ['title' => 'Digital', 'description' => 'Transformation support'],
            ['title' => 'Automation', 'description' => 'Smarter workflows'],
            ['title' => 'Experience', 'description' => 'Technology solutions expertise'],
        ],
    ],
];
$teamHighlightIcons = [
    'ACA' => '<path d="m3 9 9-5 9 5-9 5z M7 11v5c3 2 7 2 10 0v-5 M21 9v6"></path>',
    'ICAEW' => '<path d="M6 3h9l3 3v15H6z M14 3v5h4 M9 12h6 M9 16h6"></path>',
    'CPA' => '<path d="M3 21h18 M5 17v-5h4v5 M10 17V8h4v9 M15 17V3h4v14"></path>',
    '15+ Years' => '<path d="M3 8h18v12H3z M8 8V5h8v3 M3 13h18 M10 12v2h4v-2"></path>',
    'Healthcare' => '<path d="M12 21s-8-4.5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 6.5-8 11-8 11z M8 12h2l1-2 2 4 1-2h2"></path>',
    'Planning' => '<path d="M6 4h12v17H6z M9 4V2h6v2 M9 9h6 M9 13h6 M9 17h4"></path>',
    'Advisory' => '<path d="M10 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0 M20 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0 M2 21v-4a5 5 0 0 1 10 0v4 M12 21v-4a5 5 0 0 1 10 0v4"></path>',
    'Strategy' => '<path d="M4 20h16 M6 16v-4 M12 16V8 M18 16V4"></path>',
    'Growth' => '<path d="m4 17 5-5 4 3 7-8 M14 7h6v6"></path>',
    'Partnership' => '<path d="m8 12 3 3a2 2 0 0 0 3-1l2-2 M8 12l2-2a2 2 0 0 1 3 0l1 1 M4 9l4-4 4 2-4 4-4 4-3-3z M20 9l-4-4-4 2 4 4 4 4 3-3z"></path>',
    'Technology' => '<rect x="3" y="4" width="18" height="13" rx="1"></rect><path d="M8 21h8 M12 17v4"></path>',
    'Digital' => '<path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z M19 16l1 2 2 1-2 1-1 2-1-2-2-1 2-1z"></path>',
    'Automation' => '<path d="M12 3v3 M12 18v3 M3 12h3 M18 12h3 M5.6 5.6l2.1 2.1 M16.3 16.3l2.1 2.1 M18.4 5.6l-2.1 2.1 M7.7 16.3l-2.1 2.1 M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"></path>',
    'Experience' => '<path d="M12 8v4l3 2 M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>',
];
?>
<section class="section about-team" aria-labelledby="team-title">
    <div class="container">
        <div class="section-heading about-team-heading">
            <div><span class="eyebrow">Meet our team</span><h2 id="team-title">Experienced professionals, practical advice.</h2></div>
        </div>
        <ul class="about-team-grid team-grid">
            <?php foreach ($teamProfiles as $profile): ?>
                <li class="about-team-card team-card">
                    <figure class="team-portrait-space">
                        <img src="<?= htmlspecialchars($profile['image'], ENT_QUOTES, 'UTF-8') ?>" width="1280" height="853" alt="<?= htmlspecialchars($profile['alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">
                    </figure>
                    <div class="team-details">
                        <h3><?= htmlspecialchars($profile['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="team-role"><?= htmlspecialchars($profile['role'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="team-credentials"><?= htmlspecialchars($profile['credentials'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="team-biography"><?= htmlspecialchars($profile['biography'], ENT_QUOTES, 'UTF-8') ?></p>
                        <ul class="team-highlights">
                            <?php foreach ($profile['highlights'] as $highlight): ?>
                                <li>
                                    <span class="team-highlight-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><?= $teamHighlightIcons[$highlight['title']] ?></svg></span>
                                    <strong><?= htmlspecialchars($highlight['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars($highlight['description'], ENT_QUOTES, 'UTF-8') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
