<?php
$locale = $_ENV['locale'];
$localeAlt = $_ENV['locale_alt'];
$weekends = $_ENV['weekends'];
$descriptionRows = $_ENV['description_rows'];
$descriptionRowsAlt = $_ENV['description_rows_alt'];
?>
<input type="checkbox" id="ck-nav-toggle" style="display: none;" />
<label for="ck-nav-toggle" class="nav-toggle">
    <i aria-hidden="true"></i>
</label>
<nav id="page-nav" class="page-nav">
    <ul>
        <li>
            <a href="<?= $_ENV['url']('/', ['locale' => $localeAlt]) ?>"
               class="tooltip"
               data-title="<?= ($locale === 'en_GB') ? $_ENV['i18n']('Romanian') : $_ENV['i18n']('English') ?>"
            >
                <svg viewBox="0 0 32 32" width="32" height="32">
                    <use class="d" xlink:href="<?= 'images/sprite.svg#flag-' . $locale ?>" />
                    <use class="h" xlink:href="<?= 'images/sprite.svg#flag-' . $localeAlt ?>" />
                </svg>
            </a>
        </li>
        <li>
            <a href="<?= $_ENV['url']('/', ['weekends' => $weekends ? 'false' : 'true']) ?>"
               class="tooltip"
               data-title="<?= $weekends ? $_ENV['i18n']('Hide weekends') : $_ENV['i18n']('Show weekends') ?>"
            >
                <svg viewBox="0 0 32 32" width="32" height="32">
                    <use class="d" xlink:href="<?= 'images/sprite.svg#' . ($weekends ? 'w-on' : 'w-off') ?>" />
                    <use class="h" xlink:href="<?= 'images/sprite.svg#' . ($weekends ? 'w-off' : 'w-on') ?>" />
                </svg>
            </a>
        </li>
        <li>
            <a href="<?= $_ENV['url']('/', ['description_rows' => $descriptionRowsAlt]) ?>"
               class="tooltip"
               data-title="<?= ($descriptionRows === '1') ? $_ENV['i18n']('Expand description') : $_ENV['i18n']('Narrow description') ?>"
            >
                <svg viewBox="0 0 26 32" width="26" height="32">
                    <use class="d" xlink:href="<?= 'images/sprite.svg#rows-' . $descriptionRows ?>" />
                    <use class="h" xlink:href="<?= 'images/sprite.svg#rows-' . $descriptionRowsAlt ?>" />
                </svg>
            </a>
        </li>
    </ul>
</nav>
