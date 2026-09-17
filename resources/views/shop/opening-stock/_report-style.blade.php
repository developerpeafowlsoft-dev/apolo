{{-- Shared chrome for the opening stock analysis screens. Kept in one partial
     so the vendor report, a single vendor and the card detail pages agree on
     table density, the share bar and the stacked layout below lg. --}}
<style>
    .osr-kpi { border: 1px solid var(--bs-border-color-translucent, #e6e8eb); border-radius: .6rem; background: var(--bs-body-bg, #fff); padding: .7rem .85rem; height: 100%; }
    .osr-kpi .k-label { font-size: 10.5px; letter-spacing: .05em; text-transform: uppercase; color: var(--bs-secondary-color, #6c757d); font-weight: 600; }
    .osr-kpi .k-value { font-size: 1.15rem; font-weight: 700; line-height: 1.25; margin-top: .15rem; word-break: break-word; }
    .osr-kpi .k-sub   { font-size: 11px; color: var(--bs-secondary-color, #6c757d); line-height: 1.3; }
    a.osr-kpi { display: block; text-decoration: none; color: inherit; transition: box-shadow .12s, border-color .12s, transform .12s; }
    a.osr-kpi:hover { border-color: var(--bs-primary, #0d6efd); box-shadow: 0 2px 10px rgba(13,110,253,.14); transform: translateY(-1px); }
    a.osr-kpi .k-go { font-size: 10px; color: var(--bs-primary, #0d6efd); font-weight: 600; opacity: 0; transition: opacity .12s; }
    a.osr-kpi:hover .k-go { opacity: 1; }
    .osr-kpi.is-current { border-color: var(--bs-primary, #0d6efd); background: var(--bs-primary-bg-subtle, #e7f1ff); }

    /* Half a phone screen is about 165px of card, and a ten-year money figure
       is fifteen characters. Left to break-word it splits mid-number
       ("39,514,88 / 9.25"), so on the narrowest screens the value shrinks and
       is told to stay in one piece instead. */
    @media (max-width: 575.98px) {
        .osr-kpi { padding: .55rem .6rem; }
        .osr-kpi .k-label { font-size: 9.5px; }
        .osr-kpi .k-value { font-size: .95rem; word-break: normal; overflow-wrap: normal; }
        .osr-kpi .k-sub { font-size: 10px; }
    }

    /* The headline figure. Sized down on a phone so a ten-year money total
       stays on one line rather than orphaning its rupee sign. */
    .osr-hero { font-size: 2rem; line-height: 1.15; }
    @media (max-width: 575.98px) { .osr-hero { font-size: 1.35rem; } }

    /* A share of a total, drawn under the number it belongs to. */
    .osr-bar { height: 5px; border-radius: 3px; background: var(--bs-tertiary-bg, #eef1f4); overflow: hidden; }
    .osr-bar > span { display: block; height: 100%; background: var(--bs-primary, #0d6efd); border-radius: 3px; }
    .osr-bar.ok > span   { background: var(--bs-success, #198754); }
    .osr-bar.warn > span { background: var(--bs-warning, #ffc107); }
    .osr-bar.low > span  { background: var(--bs-danger, #dc3545); }

    .osr-table { font-size: 12.5px; }
    .osr-table thead th { font-size: 10.5px; letter-spacing: .02em; text-transform: uppercase; color: var(--bs-secondary-color, #6c757d); white-space: nowrap; padding: .45rem .4rem; vertical-align: bottom; }
    .osr-table tbody td { padding: .45rem .4rem; vertical-align: middle; }
    .osr-table tfoot td { padding: .55rem .4rem; font-weight: 700; border-top: 2px solid var(--bs-border-color, #dee2e6); }
    .osr-table .osr-sub { font-size: 10.5px; line-height: 1.35; color: var(--bs-secondary-color, #6c757d); }
    .osr-table thead th a { color: inherit; text-decoration: none; }
    .osr-table thead th a:hover { color: var(--bs-primary, #0d6efd); }
    .osr-table thead th.sorted a { color: var(--bs-primary, #0d6efd); }
    .osr-num { font-variant-numeric: tabular-nums; }
    .osr-code { font-family: ui-monospace, Menlo, monospace; font-size: 10.5px; }

    /* The register learned this the hard way: the shell lays the main column
       out with flex, and a flex item will not shrink below its content unless
       it is told to. Without this a wide table pushes the page, not the
       wrapper, and .table-responsive never gets to scroll. */
    @media (min-width: 992px) {
        .app-main-outer, .app-main-inner, .card, .card-body { min-width: 0; }
        .osr-wrap { max-width: 100%; }
        .osr-table td.osr-name { max-width: 240px; }
        .osr-table td.osr-name > div:first-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    }

    /* Below lg a wide table cannot be read by scrolling sideways, so each row
       becomes a labelled card. CSS only, on the one table - rendering a second
       markup block would duplicate every link and sort control. */
    @media (max-width: 991.98px) {
        .osr-table thead { display: none; }
        .osr-table, .osr-table tbody, .osr-table tfoot, .osr-table tr { display: block; width: 100%; }
        .osr-table tr { border: 1px solid var(--bs-border-color, #dee2e6); border-radius: .5rem; margin-bottom: .7rem; padding: .25rem .5rem; background: var(--bs-body-bg, #fff); }
        /* Bootstrap's .text-nowrap carries !important, so the row's nowrap has
           to be beaten with the same weapon or every value is clipped. */
        .osr-table tr, .osr-table td { white-space: normal !important; word-break: break-word; }
        .osr-wrap { overflow-x: visible !important; }
        .osr-table { min-width: 0 !important; }
        /* Grid rather than flex: a cell holds a headline and its supporting
           line as two elements, and flex would sit them side by side. */
        .osr-table td {
            display: grid; grid-template-columns: minmax(96px, 40%) 1fr; gap: 0 .75rem;
            align-items: start; border: 0; border-bottom: 1px solid var(--bs-border-color-translucent, #eee);
            padding: .5rem .25rem; text-align: right !important;
        }
        .osr-table td:last-child { border-bottom: 0; }
        .osr-table td::before {
            content: attr(data-label); grid-column: 1; grid-row: 1;
            font-weight: 600; font-size: 11px; letter-spacing: .04em; text-transform: uppercase;
            color: var(--bs-secondary-color, #6c757d); text-align: left;
        }
        .osr-table td > * { grid-column: 2; justify-self: end; }
        .osr-table td:not([data-label]) { grid-template-columns: 1fr; }
        .osr-table td:not([data-label])::before { content: none; }
        .osr-table td:not([data-label]) > * { grid-column: 1; justify-self: start; }
        .osr-bar { min-width: 90px; }
    }

    /* On a phone the shell's icon rail and four levels of container padding
       leave the card about 208px of usable width. A label column of 96px plus
       a figure like "2,178,517.85" needs 226px, so the row was overflowing its
       own cell and losing the right-hand digits. Below this width the label
       goes above its value instead of beside it, which gives the number the
       full card to sit in. */
    @media (max-width: 575.98px) {
        .osr-table td {
            grid-template-columns: 1fr;
            gap: 0;
            padding: .45rem .15rem;
            text-align: left !important;
        }
        .osr-table td::before { grid-row: auto; margin-bottom: .1rem; }
        .osr-table td > * { grid-column: 1; justify-self: start; }
        .osr-table td .osr-bar { min-width: 0; width: 100%; }
    }
</style>
