<?php
$bookkeepingCards = [
    [
        'title' => 'Bookkeeping & reconciliations',
        'description' => 'Maintain sales, purchase and expense records. Reconcile bank accounts, supplier and customer balances, and general ledgers.',
        'image' => '/assets/images/book1.png',
        'alt' => 'Bookkeeping and financial records',
        'icon' => '<path d="M6 3h9l3 3v15H6z M14 3v5h4 M9 12h6 M9 16h6"></path>',
    ],
    [
        'title' => 'Annual financial statements',
        'description' => 'Prepare year-end accounts, supporting schedules and adjustments for applicable Irish reporting and filing requirements.',
        'image' => '/assets/images/book2.png',
        'alt' => 'Financial statements and reports',
        'icon' => '<path d="M3 21h18 M5 17v-5h4v5 M10 17V8h4v9 M15 17V3h4v14"></path>',
    ],
    [
        'title' => 'Management accounts',
        'description' => 'Monthly or quarterly reporting, cash-flow information and financial analysis to help you monitor performance and plan ahead.',
        'image' => '/assets/images/book3.png',
        'alt' => 'Management accounts and financial analysis',
        'icon' => '<path d="M9 3h6l1 3 3 1 2 5-2 2-1 4-4 3-3-2-4-1-3-5 2-3 1-4z M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0"></path>',
    ],
    [
        'title' => 'Accounting systems',
        'description' => 'Set up and support the use of suitable cloud accounting software, with training and ongoing assistance.',
        'image' => '/assets/images/book4.png',
        'alt' => 'Cloud accounting systems',
        'icon' => '<path d="M7 19a5 5 0 0 1-1-10 6 6 0 0 1 12-1 5 5 0 0 1 0 11z"></path>',
    ],
];
?>
<ul class="detail-list service-detail-list accounting-details" data-astro-cid-z7xolria>
    <?php foreach ($bookkeepingCards as $card): ?>
        <li class="detail-item" data-astro-cid-z7xolria>
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
