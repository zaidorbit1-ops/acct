<?php
declare(strict_types=1);

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

require __DIR__ . '/includes/backend.php';
backend_dispatch();

$pagesPath = __DIR__ . '/data/pages.json';
$pagesJson = file_get_contents($pagesPath);
if ($pagesJson === false) {
    throw new RuntimeException('Unable to read the page content file.');
}

$pages = json_decode($pagesJson, true, 512, JSON_THROW_ON_ERROR);
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$slug = trim(rawurldecode(is_string($requestPath) ? $requestPath : '/'), '/');
$slug = $slug === '' ? 'home' : $slug;

$legacyPageRedirects = [
    'pension-claim-administration' => 'pension-claim-assistance-tax-relief',
];
if (isset($legacyPageRedirects[$slug])) {
    header('Location: /' . $legacyPageRedirects[$slug], true, 301);
    exit;
}

if (!preg_match('/^[a-z0-9-]+$/', $slug) || !isset($pages[$slug])) {
    http_response_code(404);
    $slug = '404';
} elseif ($slug === '404') {
    http_response_code(404);
}

$page = $pages[$slug];
$servicePageComponents = require __DIR__ . '/data/service-pages.php';
$isServicePage = isset($servicePageComponents[$slug])
    || strpos($page['main'], 'class="service-hero') !== false;
$uppercaseFirstCharacter = static function (string $text): string {
    $result = preg_replace_callback(
        '/^(\s*(?:<[^>]+>\s*)*)([a-z])/i',
        static fn(array $match): string => $match[1] . strtoupper($match[2]),
        $text,
        1
    );

    if ($result === null) {
        throw new RuntimeException('Unable to capitalize a hero heading.');
    }

    return $result;
};
$redesignPageClasses = [
    'industries' => 'page-industries',
    'contact' => 'page-contact',
    'careers' => 'page-careers',
];
$pageBodyClass = $slug === 'home'
    ? 'page-home'
    : ($slug === 'about-us' ? 'page-about' : ($isServicePage ? 'page-service' : ($redesignPageClasses[$slug] ?? '')));
$mainMarkup = $page['main'];

if ($slug === 'careers') {
    try {
        $mainMarkup = backend_public_jobs_markup($mainMarkup);
    } catch (Throwable $exception) {
        error_log('Public career page failed to load vacancies: ' . $exception->getMessage());
        http_response_code(503);
        $mainMarkup = backend_unavailable_jobs_markup($mainMarkup);
    }
}
if ($slug === 'contact') {
    $enabledFormMarkup = str_replace('data-enabled="false"', 'data-enabled="true"', $mainMarkup, $enabledFormCount);
    if ($enabledFormCount !== 1) {
        throw new RuntimeException('Unable to enable the contact form.');
    }
    $mainMarkup = str_replace(
        'pattern="[+0-9\s().\-]+"',
        'pattern="(?:\+|[0-9]|\s|\(|\)|\.|-)+"',
        $enabledFormMarkup,
        $phonePatternCount
    );
    if ($phonePatternCount !== 1) {
        throw new RuntimeException('Unable to prepare the contact telephone field.');
    }
    $mainMarkup = str_replace(
        'A brief overview is enough. We will explain how to share any records securely.',
        'A brief overview of at least 10 characters is enough. We will explain how to share any records securely.',
        $mainMarkup,
        $messageHintCount
    );
    if ($messageHintCount !== 1) {
        throw new RuntimeException('Unable to update the contact message guidance.');
    }
}

if ($slug === 'about-us') {
    $teamMarkup = preg_replace(
        '/<section\b(?=[^>]*\bclass="[^"]*\babout-team\b)[^>]*>[\s\S]*?<\/section>/i',
        '<!--ABOUT_TEAM_COMPONENT-->',
        $mainMarkup,
        1,
        $teamSectionCount
    );
    if ($teamMarkup === null || $teamSectionCount !== 1) {
        throw new RuntimeException('Unable to replace the About team section.');
    }
    $mainMarkup = $teamMarkup;
    $mainMarkup = preg_replace(
        '/<figure\b(?=[^>]*\bclass="[^"]*\boffice-concept\b[^"]*")[^>]*>[\s\S]*?<\/figure>/i',
        '',
        $mainMarkup,
        1,
        $officeConceptCount
    );
    if ($mainMarkup === null || $officeConceptCount !== 1) {
        throw new RuntimeException('Unable to remove the About page office concept image.');
    }
}

if ($slug === 'accounting-services') {
    $bookkeepingMarkup = preg_replace(
        '/<ul\b(?=[^>]*\bclass="[^"]*\baccounting-details\b)[^>]*>[\s\S]*?<\/ul>/i',
        '<!--BOOKKEEPING_CARDS_COMPONENT-->',
        $mainMarkup,
        1,
        $bookkeepingSectionCount
    );
    if ($bookkeepingMarkup === null || $bookkeepingSectionCount !== 1) {
        throw new RuntimeException('Unable to replace the bookkeeping service cards.');
    }
    $mainMarkup = $bookkeepingMarkup;
}

if ($slug === 'it-software-solutions') {
    $itCardsMarkup = preg_replace(
        '/<ul\b(?=[^>]*\bclass="[^"]*\bservice-detail-list\b)[^>]*>[\s\S]*?<\/ul>/i',
        '<!--IT_SERVICE_CARDS_COMPONENT-->',
        $mainMarkup,
        1,
        $itCardsSectionCount
    );
    if ($itCardsMarkup === null || $itCardsSectionCount !== 1) {
        throw new RuntimeException('Unable to replace the IT service cards.');
    }
    $mainMarkup = $itCardsMarkup;
}

if ($slug === 'home') {
    $serviceCardImages = [
        '/accounting-services' => '/assets/images/home-card-1.png',
        '/taxation-services' => '/assets/images/home-card-2.png',
        '/business-consultancy' => '/assets/images/home-card-3.png',
        '/company-formation' => '/assets/images/home-card-4.png',
        '/payroll-services' => '/assets/images/home-card-5.png',
        '/it-software-solutions' => '/assets/images/home-card-6.png',
    ];

    foreach ($serviceCardImages as $href => $imagePath) {
        $cardOpeningTag = '<a class="service-card" href="' . $href . '">';
        if (substr_count($mainMarkup, $cardOpeningTag) !== 1) {
            throw new RuntimeException('Unable to add a homepage service card image for ' . $href . '.');
        }
        $serviceCardImage = '<figure class="service-card-image"><img src="' . $imagePath . '" width="1280" height="720" alt="" loading="lazy" decoding="async"></figure>';
        $mainMarkup = str_replace($cardOpeningTag, $cardOpeningTag . $serviceCardImage, $mainMarkup);
    }
}

if ($slug === 'home') {
    $homeHeroMarkup = preg_replace(
        '/<section\b[^>]*class="[^"]*\bhome-hero\b[^"]*"[^>]*>[\s\S]*?<\/section>/i',
        '<!--HOME_HERO_COMPONENT-->',
        $mainMarkup,
        1,
        $homeHeroCount
    );
    if ($homeHeroMarkup === null || $homeHeroCount !== 1) {
        throw new RuntimeException('Unable to replace the homepage hero section.');
    }
    $mainMarkup = $homeHeroMarkup;
}

if ($slug === 'about-us') {
    $aboutHeroMarkup = preg_replace(
        '/<section\b[^>]*class="[^"]*\babout-hero\b[^"]*"[^>]*>[\s\S]*?<\/section>/i',
        '<!--ABOUT_HERO_COMPONENT-->',
        $mainMarkup,
        1,
        $aboutHeroCount
    );
    if ($aboutHeroMarkup === null || $aboutHeroCount !== 1) {
        throw new RuntimeException('Unable to replace the About page hero section.');
    }
    $mainMarkup = $aboutHeroMarkup;
}

if ($slug === 'home' || $slug === 'about-us' || $isServicePage) {
    $normalizedMarkup = preg_replace_callback(
        '/<h1\b[^>]*>.*?<\/h1>/is',
        static fn(array $match): string => $uppercaseFirstCharacter($match[0]),
        $mainMarkup
    );
    if ($normalizedMarkup === null) {
        throw new RuntimeException('Unable to capitalize a hero heading.');
    }
    $mainMarkup = $normalizedMarkup;

    if (isset($servicePageComponents[$slug]['hero']['title'])) {
        $servicePageComponents[$slug]['hero']['title'] = $uppercaseFirstCharacter($servicePageComponents[$slug]['hero']['title']);
    }
}

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
        <link rel="stylesheet" href="/assets/css/about.css?v=<?= filemtime(__DIR__ . '/assets/css/about.css') ?>">
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
    '<!--HOME_HERO_COMPONENT-->' => __DIR__ . '/includes/components/home-hero.php',
    '<!--ABOUT_HERO_COMPONENT-->' => __DIR__ . '/includes/components/about-hero.php',
    '<!--ABOUT_TEAM_COMPONENT-->' => __DIR__ . '/includes/components/about-team.php',
    '<!--SERVICE_HERO_COMPONENT-->' => __DIR__ . '/includes/components/service-hero.php',
    '<!--BOOKKEEPING_CARDS_COMPONENT-->' => __DIR__ . '/includes/components/bookkeeping-cards.php',
    '<!--IT_SERVICE_CARDS_COMPONENT-->' => __DIR__ . '/includes/components/it-service-cards.php',
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
