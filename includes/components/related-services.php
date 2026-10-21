<?php
$servicePage = $servicePageComponents[$slug] ?? null;
if (is_array($servicePage) && isset($servicePage['related_markup'])) {
    echo $servicePage['related_markup'];
    return;
}
if (!is_array($servicePage) || !isset($servicePage['related']['items'])) {
    throw new RuntimeException('Related services component data is missing.');
}
$relatedServices = $servicePage['related'];
?>
<section class="section service-related-section" aria-labelledby="related-services-title">
    <div class="container">
        <div class="service-related-heading">
            <span class="eyebrow"><?= htmlspecialchars($relatedServices['eyebrow'], ENT_QUOTES, 'UTF-8') ?></span>
            <h2 id="related-services-title"><?= htmlspecialchars($relatedServices['title'], ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <div class="related-grid service-related-grid">
            <?php foreach ($relatedServices['items'] as $item): ?>
                <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>">
                    <span class="related-symbol" aria-hidden="true">
                        <svg class="service-icon" viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <?= $item['icon'] ?>
                        </svg>
                    </span>
                    <span>
                        <strong><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span class="related-description"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></span>
                    </span>
                    <svg class="direction-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M4 12h16 M14 6l6 6-6 6"></path>
                    </svg>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
