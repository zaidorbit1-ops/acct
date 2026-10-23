<?php
$itServiceCards = [
    [
        'id' => 'support-business-applications',
        'title' => 'Business applications',
        'description' => 'Discuss custom applications, responsive websites, business portals and e-commerce requirements in the context of your operations.',
        'image' => '/assets/images/it1.png',
        'alt' => 'Business applications and web development',
        'icon' => '<path d="M3 4h18v13H3z M8 21h8 M12 17v4 M9 8l-3 3 3 3 M15 8l3 3-3 3"></path>',
    ],
    [
        'id' => 'support-integration-and-automation',
        'title' => 'Integration & automation',
        'description' => 'Review how business applications, accounting systems, databases and APIs can work together to reduce repeated manual tasks.',
        'image' => '/assets/images/it2.png',
        'alt' => 'Connected systems and workflow automation',
        'icon' => '<path d="M9 3h6l1 3 3 1 2 5-2 2-1 4-4 3-3-2-4-1-3-5 2-3 1-4z M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0"></path>',
    ],
    [
        'id' => 'support-quality-and-maintenance',
        'title' => 'Quality & maintenance',
        'description' => 'Plan testing, debugging, documentation, maintenance and ongoing improvements as part of an agreed delivery scope.',
        'image' => '/assets/images/it3.png',
        'alt' => 'Software quality assurance and maintenance',
        'icon' => '<path d="M10 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0 M20 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0 M2 21v-4a5 5 0 0 1 10 0v4 M12 21v-4a5 5 0 0 1 10 0v4"></path>',
    ],
];
?>
<ul class="detail-list service-detail-list it-support-details" data-astro-cid-z7xolria>
    <?php foreach ($itServiceCards as $card): ?>
        <li class="detail-item" id="<?= htmlspecialchars($card['id'], ENT_QUOTES, 'UTF-8') ?>" data-astro-cid-z7xolria>
            <img class="detail-card-image" src="<?= htmlspecialchars($card['image'], ENT_QUOTES, 'UTF-8') ?>" width="1280" height="853" alt="<?= htmlspecialchars($card['alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">
            <span class="detail-symbol" data-astro-cid-z7xolria>
                <svg class="service-icon" viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><?= $card['icon'] ?></svg>
            </span>
            <div data-astro-cid-z7xolria>
                <h3 data-astro-cid-z7xolria><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p data-astro-cid-z7xolria><?= htmlspecialchars($card['description'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
