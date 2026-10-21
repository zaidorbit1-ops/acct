<?php
$headerMarkup = <<<'HTML'
<a class="skip-link" href="#main">Skip to content</a>
<div class="utility">
    <div class="container flex items-center justify-between">
        <p class="utility-services">Accounting | Business Advisory | Technology</p>
        <div class="utility-contact">
            <span>Maynooth, Co. Kildare</span>
            <a href="tel:+35345254212">045 254212</a>
            <a href="mailto:info@acctechs.ie">info@acctechs.ie</a>
        </div>
    </div>
</div>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/" aria-label="ACCTS &amp; TECHS LTD | Home">
            <img src="/assets/images/at-mark.png" width="56" height="40" alt="">
            <span><strong>ACCTS &amp; TECHS LTD</strong><small>TAX • BUSINESS ADVISORY • IT SOLUTIONS</small></span>
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
                            <a href="/financial-consultancy">Financial consultancy</a>
                            <a href="/outsourced-cfo-services">Outsourced CFO</a>
                            <a href="/management-reporting">Management reporting</a>
                            <a href="/growth-restructuring">Growth &amp; restructuring</a>
                            <a href="/mergers-acquisitions">Mergers &amp; acquisitions</a>
                        </div>
                        <div>
                            <p class="nav-heading">
                                <span class="nav-category-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 3h14v18H5zM8 7h8M8 11h8M8 15h5"></path></svg></span>
                                <span>Accounting &amp; Tax</span>
                            </p>
                            <a href="/accounting-services">Bookkeeping &amp; accounts</a>
                            <a href="/corporation-tax">Corporation tax</a>
                            <a href="/income-tax-form-11">Income tax &amp; Form 11</a>
                            <a href="/vat-services">VAT services</a>
                            <a href="/payroll-services">Payroll &amp; PAYE</a>
                            <a href="/capital-gains-tax">Capital gains tax (CGT)</a>
                            <a href="/inheritance-tax-cat">Inheritance tax (CAT)</a>
                        </div>
                        <div>
                            <p class="nav-heading">
                                <span class="nav-category-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="13" rx="2"></rect><path d="M8 21h8m-4-4v4m-3-9 2-2-2-2m4 4h3"></path></svg></span>
                                <span>Technology</span>
                            </p>
                            <a href="/it-software-solutions">IT consultancy &amp; integration</a>
                            <a href="/software-development">Software solutions</a>
                            <a href="/cloud-accounting">Cloud accounting</a>
                            <a href="/digital-transformation">Digital transformation</a>
                            <a href="/it-support-services">IT support services</a>
                            <a href="/cyber-security-advisory">Cyber security advisory</a>
                        </div>
                        <div>
                            <p class="nav-heading">
                                <span class="nav-category-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 21h16M6 18V7l6-4 6 4v11M9 10h6m-6 4h6"></path></svg></span>
                                <span>Company Services</span>
                            </p>
                            <a href="/company-formation">Company formations</a>
                            <a href="/company-secretarial">Company secretarial</a>
                            <a href="/cro-filings-compliance">CRO filings &amp; compliance</a>
                            <a href="/registered-office">Registered office</a>
                            <a href="/compliance-governance">Compliance &amp; governance</a>
                            <a href="/business-restructuring">Business restructuring</a>
                        </div>
                        <div>
                            <p class="nav-heading">
                                <span class="nav-category-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 20v-2a6 6 0 0 1 12 0v2m2-10a3 3 0 1 0 0-6m4 16v-2a6 6 0 0 0-4-5.7"></path></svg></span>
                                <span>Individuals &amp; Non-Residents</span>
                            </p>
                            <a href="/personal-tax-advice">Personal tax advice</a>
                            <a href="/pps-number-application-services">PPSN applications</a>
                            <a href="/paye-tax-refund-services">PAYE refunds</a>
                            <a href="/pension-claim-administration">Pension administration</a>
                            <a href="/tax-non-residents">Tax for non-residents</a>
                            <a href="/expat-tax-services">Expert tax services</a>
                        </div>
                    </div>
                </div>
            </details>
            <a href="/industries">Industries</a>
            <a href="/careers">Careers</a>
            <a href="/contact">Contact</a>
            <a class="button button-small" href="/contact">Let’s talk</a>
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
        $megaLinks = substr($nav, $megaStart);
        if (preg_match($currentLinkPattern, $megaLinks)) {
            $nav = preg_replace('~<details class="services-menu"~', '<details class="services-menu has-current-page"', $nav, 1);
        }
    }

    $nav = preg_replace($currentLinkPattern, '$1 aria-current="page"', $nav, 1);
    $headerMarkup = substr_replace($headerMarkup, $nav, $navStart, $navEnd - $navStart);
}

echo $headerMarkup;
?>
