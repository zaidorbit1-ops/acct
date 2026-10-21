<?php
declare(strict_types=1);

$pagesPath = __DIR__ . '/data/pages.json';
$pagesJson = file_get_contents($pagesPath);
if ($pagesJson === false) {
    throw new RuntimeException('Unable to read the page content file.');
}

$pages = json_decode($pagesJson, true, 512, JSON_THROW_ON_ERROR);
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$slug = trim(rawurldecode(is_string($requestPath) ? $requestPath : '/'), '/');
$slug = $slug === '' ? 'home' : $slug;

if (!preg_match('/^[a-z0-9-]+$/', $slug) || !isset($pages[$slug])) {
    http_response_code(404);
    $slug = '404';
} elseif ($slug === '404') {
    http_response_code(404);
}

$page = $pages[$slug];
$servicePageComponents = require __DIR__ . '/data/service-pages.php';
$isServicePage = isset($servicePageComponents[$slug]) || strpos($page['main'], 'class="service-hero') !== false;
$redesignPageClasses = [
    'industries' => 'page-industries',
    'contact' => 'page-contact',
    'careers' => 'page-careers',
];
$pageBodyClass = $slug === 'home'
    ? 'page-home'
    : ($slug === 'about-us' ? 'page-about' : ($isServicePage ? 'page-service' : ($redesignPageClasses[$slug] ?? '')));
$mainMarkup = $page['main'];

$appendComponentClass = static function (string $markup, string $tagName, string $className): string {
    $tagPattern = '/<' . preg_quote($tagName, '/') . '\b[^>]*>/i';
    $result = preg_replace_callback($tagPattern, static function (array $tagMatch) use ($className): string {
        if (!preg_match('/\bclass="([^"]*)"/i', $tagMatch[0], $classMatch)) {
            return preg_replace('/>$/', ' class="' . $className . '">', $tagMatch[0]) ?? $tagMatch[0];
        }

        if (preg_match('/(?:^|\s)' . preg_quote($className, '/') . '(?:\s|$)/', $classMatch[1])) {
            return $tagMatch[0];
        }

        return str_replace($classMatch[0], 'class="' . $classMatch[1] . ' ' . $className . '"', $tagMatch[0]);
    }, $markup, 1);

    if ($result === null) {
        throw new RuntimeException('Unable to add a service component class.');
    }

    return $result;
};

if ($isServicePage) {
    preg_match_all('/<section\b[^>]*>[\s\S]*?<\/section>/i', $mainMarkup, $sectionMatches, PREG_OFFSET_CAPTURE);
    $replacements = [];
    $helpSectionFound = false;

    foreach ($sectionMatches[0] as [$sectionMarkup, $sectionOffset]) {
        $originalSectionLength = strlen($sectionMarkup);

        if (preg_match('/<section\b[^>]*class="[^"]*\bservice-hero\b/i', $sectionMarkup)) {
            if (strpos($sectionMarkup, '<!--SERVICE_HERO_COMPONENT-->') === false) {
                $heroMarkup = $appendComponentClass($sectionMarkup, 'section', 'service-hero-component');
                $heroMarkup = $appendComponentClass($heroMarkup, 'section', 'service-hero-layout');
                $heroMarkup = $appendComponentClass($heroMarkup, 'div', 'service-hero-copy');
                $heroMarkup = $appendComponentClass($heroMarkup, 'figure', 'service-hero-photo');
                $servicePageComponents[$slug]['hero_markup'] = $heroMarkup;
                $replacements[] = [$sectionOffset, strlen($sectionMarkup), '<!--SERVICE_HERO_COMPONENT-->'];
            }
            continue;
        }

        if (stripos($sectionMarkup, 'Related services') !== false) {
            if (strpos($sectionMarkup, '<!--RELATED_SERVICES_COMPONENT-->') === false) {
                $relatedMarkup = $appendComponentClass($sectionMarkup, 'section', 'service-related-section');
                $relatedMarkup = preg_replace_callback('/<div\b[^>]*class="([^"]*\brelated-grid\b[^"]*)"[^>]*>/i', static function (array $match): string {
                    if (preg_match('/(?:^|\s)service-related-grid(?:\s|$)/', $match[1])) {
                        return $match[0];
                    }
                    return str_replace($match[1], $match[1] . ' service-related-grid', $match[0]);
                }, $relatedMarkup, 1) ?? $relatedMarkup;
                $servicePageComponents[$slug]['related_markup'] = $relatedMarkup;
                $replacements[] = [$sectionOffset, $originalSectionLength, '<!--RELATED_SERVICES_COMPONENT-->'];
            }
            continue;
        }

        if (preg_match('/\bprocess-marker\b/', $sectionMarkup)) {
            $sectionMarkup = $appendComponentClass($sectionMarkup, 'section', 'service-engagement');
            $sectionMarkup = preg_replace_callback('/(<div\b[^>]*class="[^"]*\bprocess-marker\b[^"]*"[^>]*>)([\s\S]*?)(<\/div>)/i', static function (array $markerMatch): string {
                $markerContent = preg_replace('/<span\b[^>]*>\s*\d+\s*<\/span>/i', '', $markerMatch[2]) ?? $markerMatch[2];
                return $markerMatch[1] . $markerContent . $markerMatch[3];
            }, $sectionMarkup) ?? $sectionMarkup;
            $replacements[] = [$sectionOffset, $originalSectionLength, $sectionMarkup];
            continue;
        }

        if (!$helpSectionFound && stripos($sectionMarkup, 'detail-layout') !== false) {
            $helpMarkup = $appendComponentClass($sectionMarkup, 'section', 'service-help');
            $replacements[] = [$sectionOffset, $originalSectionLength, $helpMarkup];
            $helpSectionFound = true;
        }
    }

    usort($replacements, static fn(array $a, array $b): int => $b[0] <=> $a[0]);
    foreach ($replacements as [$offset, $length, $replacement]) {
        $mainMarkup = substr_replace($mainMarkup, $replacement, $offset, $length);
    }
}
?>
<!doctype html>
<html lang="en-IE">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="preload" href="/assets/fonts/manrope-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/css/site.css">
    <link rel="stylesheet" href="/assets/css/navigation.css">
    <link rel="stylesheet" href="/assets/css/buttons.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <?php if ($slug === 'about-us'): ?>
        <link rel="stylesheet" href="/assets/css/about.css">
    <?php endif; ?>
    <?php if ($isServicePage): ?>
        <link rel="stylesheet" href="/assets/css/service.css">
    <?php endif; ?>
    <?php if (isset($redesignPageClasses[$slug])): ?>
        <link rel="stylesheet" href="/assets/css/redesign-pages.css">
    <?php endif; ?>
    <?= $page['head'] ?>
</head>
<body class="<?= htmlspecialchars($pageBodyClass, ENT_QUOTES, 'UTF-8') ?>">
<?php
require __DIR__ . '/includes/header.php';

$componentFiles = [
    '<!--SERVICE_HERO_COMPONENT-->' => __DIR__ . '/includes/components/service-hero.php',
    '<!--RELATED_SERVICES_COMPONENT-->' => __DIR__ . '/includes/components/related-services.php',
    '<!--FAQ_COMPONENT-->' => __DIR__ . '/includes/components/faq.php',
];
$componentPattern = '/(' . implode('|', array_map(static fn(string $marker): string => preg_quote($marker, '/'), array_keys($componentFiles))) . ')/';
$mainParts = preg_split($componentPattern, $mainMarkup, -1, PREG_SPLIT_DELIM_CAPTURE);
if ($mainParts === false) {
    throw new RuntimeException('Unable to split page components.');
}

foreach ($mainParts as $part) {
    if (isset($componentFiles[$part])) {
        require $componentFiles[$part];
    } else {
        echo $part;
    }
}

require __DIR__ . '/includes/footer.php';

foreach (array_merge(['site.js'], $page['scripts']) as $script) {
    echo '<script type="module" src="/assets/js/' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '"></script>';
}
?>
</body>
</html>
