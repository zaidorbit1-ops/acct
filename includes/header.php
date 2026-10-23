<?php
$headerMarkup = <<<'HTML'
<a class="skip-link" href="#main">Skip to content</a>
<div class="utility">
    <div class="container flex items-center justify-between">
        <p class="utility-services">Accounting | Business Advisory | Technology</p>
        <div class="utility-contact">
            <span class="utility-item utility-location">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                    <circle cx="12" cy="10" r="2.5"></circle>
                </svg>
                <span>Maynooth, Co. Kildare</span>
            </span>
            <a class="utility-item" href="tel:+35345254212">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7 3H4a1 1 0 0 0-1 1c0 9.4 7.6 17 17 17a1 1 0 0 0 1-1v-3l-5-2-2 3a14 14 0 0 1-8-8l3-2-2-5Z"></path>
                </svg>
                <span>045 254212</span>
            </a>
            <a class="utility-item" href="mailto:info@acctechs.ie">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                    <path d="m3 7 9 6 9-6"></path>
                </svg>
                <span>info@acctechs.ie</span>
            </a>
        </div>
    </div>
</div>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/" aria-label="ACCTS &amp; TECHS LTD | Home">
            <img class="brand-logo" src="/assets/images/logo.png" width="240" height="46" alt="">
        </a>
        <button id="menu-toggle" class="menu-toggle" aria-expanded="false" aria-controls="primary-navigation">
            Menu
            <svg aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <nav id="primary-navigation" aria-label="Main navigation">
            <a href="/">Home</a>
            <a href="/about-us">About us</a>
            <details class="services-menu">
                <summary>
                    Our services
                    <svg class="menu-indicator" aria-hidden="true" viewBox="0 0 20 20" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m5 7.5 5 5 5-5"></path>
                    </svg>
                </summary>
                <div class="mega-menu">
                    <div class="mega-groups">
                        <div>
                            <p class="nav-heading">
                                <span class="nav-category-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 20h18M5 17v-5h4v5M10 17V8h4v9M15 17V4h4v13"></path></svg></span>
                                <span>Business Advisory</span>
                            </p>
                            <a href="/business-consultancy">Business planning &amp; strategy</a>
                            <a href="/financial-consultancy">Financial planning &amp; analysis</a>
                            <a href="/outsourced-cfo-services">Outsourced CFO services</a>
                            <a href="/management-reporting">Management reporting</a>
                            <a href="/growth-restructuring">Business growth &amp; restructuring</a>
                            <a href="/mergers-acquisitions">Business performance review</a>
                        </div>
                        <div>
                            <p class="nav-heading">
                                <span class="nav-category-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 3h14v18H5zM8 7h8M8 11h8M8 15h5"></path></svg></span>
                                <span>Accounting &amp; Tax</span>
                            </p>
                            <a href="/accounting-services">Bookkeeping &amp; financial accounts</a>
                            <a href="/corporation-tax">Corporation tax</a>
                            <a href="/income-tax-form-11">Income tax &amp; Form 11 returns</a>
                            <a href="/vat-services">VAT compliance &amp; returns</a>
                            <a href="/payroll-services">Payroll &amp; PAYE services</a>
                            <a href="/capital-gains-tax">Capital gains tax (CGT)</a>
                            <a href="/inheritance-tax-cat">Capital acquisitions tax (CAT)</a>
                        </div>
                        <div>
                            <p class="nav-heading">
                                <span class="nav-category-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="13" rx="2"></rect><path d="M8 21h8m-4-4v4m-3-9 2-2-2-2m4 4h3"></path></svg></span>
                                <span>Technology</span>
                            </p>
                            <a href="/it-software-solutions">IT consultancy &amp; systems integration</a>
                            <a href="/software-development">Software development &amp; solutions</a>
                            <a href="/cloud-accounting">Cloud accounting solutions</a>
                            <a href="/digital-transformation">Digital transformation</a>
                            <a href="/it-support-services">IT support &amp; maintenance</a>
                            <a href="/cyber-security-advisory">Cybersecurity advisory</a>
                        </div>
                        <div>
                            <p class="nav-heading">
                                <span class="nav-category-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 21h16M6 18V7l6-4 6 4v11M9 10h6m-6 4h6"></path></svg></span>
                                <span>Company Services</span>
                            </p>
                            <a href="/company-formation">Company formation &amp; registration</a>
                            <a href="/company-secretarial">Company secretarial services</a>
                            <a href="/cro-filings-compliance">CRO annual returns &amp; filings</a>
                            <a href="/registered-office">Registered office services</a>
                            <a href="/compliance-governance">Corporate compliance &amp; governance</a>
                            <a href="/business-restructuring">Company changes &amp; restructuring</a>
                        </div>
                        <div>
                            <p class="nav-heading">
                                <span class="nav-category-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 20v-2a6 6 0 0 1 12 0v2m2-10a3 3 0 1 0 0-6m4 16v-2a6 6 0 0 0-4-5.7"></path></svg></span>
                                <span>Individuals &amp; Non-Residents</span>
                            </p>
                            <a href="/personal-tax-advice">Personal tax services</a>
                            <a href="/pps-number-application-services">PPSN application assistance</a>
                            <a href="/paye-tax-refund-services">PAYE tax refunds</a>
                            <a href="/pension-claim-assistance-tax-relief">Pension claim assistance &amp; tax relief</a>
                            <a href="/tax-non-residents">Non-resident tax services</a>
                            <a href="/expat-tax-services">Expatriate tax services</a>
                            <a href="/contact?service=work-permit-support">Work permit support (administrative)</a>
                        </div>
                    </div>
                </div>
            </details>
            <a href="/industries">Industries</a>
            <a href="/careers">Careers</a>
            <a href="/contact">Contact</a>
            <a class="button button-small header-cta" href="/contact">Let’s talk</a>
        </nav>
    </div>
</header>
HTML;

$headerMarkup = preg_replace('~\saria-current="page"~', '', $headerMarkup);
$navStart = strpos($headerMarkup, '<nav id="primary-navigation"');
$navEnd = strpos($headerMarkup, '</nav>', $navStart === false ? 0 : $navStart);

if ($navStart !== false && $navEnd !== false && $slug !== '404') {
    $nav = substr($headerMarkup, $navStart, $navEnd - $navStart);
    $currentHref = $slug === 'home' ? '/' : '/' . $slug;
    $currentLinkPattern = '~(<a href="' . preg_quote($currentHref, '~') . '")~';

    if (strpos($nav, 'class="mega-groups"') !== false) {
        $megaStart = strpos($nav, 'class="mega-groups"');
        $megaEnd = strpos($nav, '</details>', $megaStart);
        $megaLinks = $megaEnd === false
            ? substr($nav, $megaStart)
            : substr($nav, $megaStart, $megaEnd - $megaStart);
        if (preg_match($currentLinkPattern, $megaLinks)) {
            $nav = preg_replace('~<details class="services-menu"~', '<details class="services-menu has-current-page"', $nav, 1);
        }
    }

    $nav = preg_replace($currentLinkPattern, '$1 aria-current="page"', $nav, 1);
    $headerMarkup = substr_replace($headerMarkup, $nav, $navStart, $navEnd - $navStart);
}

echo $headerMarkup;
?>
