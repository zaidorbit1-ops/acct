<?php
$servicePage = $servicePageComponents[$slug] ?? null;
if (is_array($servicePage) && isset($servicePage['hero_markup'])) {
    echo $servicePage['hero_markup'];
    return;
}
if (!is_array($servicePage) || !isset($servicePage['hero'])) {
    throw new RuntimeException('Service hero component data is missing.');
}
$hero = $servicePage['hero'];
?>
<section class="service-hero service-hero-component" aria-labelledby="service-page-title">
    <div class="service-hero-layout">
        <div class="service-hero-copy">
            <span class="eyebrow"><?= htmlspecialchars($hero['eyebrow'], ENT_QUOTES, 'UTF-8') ?></span>
            <h1 id="service-page-title"><?= htmlspecialchars($hero['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="service-intro"><?= htmlspecialchars($hero['intro'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="lead"><?= htmlspecialchars($hero['lead'], ENT_QUOTES, 'UTF-8') ?></p>
            <div class="hero-actions">
                <a class="button" href="<?= htmlspecialchars($hero['button_href'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($hero['button_label'], ENT_QUOTES, 'UTF-8') ?>
                    <svg class="direction-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M4 12h16 M14 6l6 6-6 6"></path>
                    </svg>
                </a>
            </div>
        </div>
        <figure class="photo service-hero-photo">
            <img
                src="<?= htmlspecialchars($hero['image'], ENT_QUOTES, 'UTF-8') ?>"
                srcset="<?= htmlspecialchars($hero['srcset'], ENT_QUOTES, 'UTF-8') ?>"
                sizes="<?= htmlspecialchars($hero['sizes'], ENT_QUOTES, 'UTF-8') ?>"
                width="1280"
                height="853"
                alt="<?= htmlspecialchars($hero['alt'], ENT_QUOTES, 'UTF-8') ?>"
                loading="eager"
                fetchpriority="high"
                decoding="async">
        </figure>
    </div>
</section>
