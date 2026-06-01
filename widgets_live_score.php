<style>
    .horizontal-scroll-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
        margin-top: 15px;
        padding-bottom: 10px; /* Tambahkan sedikit padding untuk scrollbar */
    }
    .widget-item {
        display: inline-block;
        width: 90%; /* Mengambil 90% lebar viewport */
        max-width: 400px; /* Lebar maksimal untuk layar desktop */
        margin-right: 15px;
        vertical-align: top;
        white-space: normal;
        background-color: var(--secondaryBackground);
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        padding: 10px;
        min-height: 400px; /* Minimal tinggi agar terlihat seragam */
    }
    @media (min-width: 640px) {
        .widget-item {
            width: 45%;
        }
    }
    @media (min-width: 1024px) {
        .widget-item {
            width: 30%;
        }
    }
    .widget-item:last-child {
        margin-right: 0;
    }
    .widget-title {
        font-weight: bold;
        font-size: 1.25rem;
        margin-bottom: 10px;
        text-align: center;
    }
</style>

<div class="horizontal-scroll-wrapper">
    <div class="widget-item">
        <p class="widget-title">Live Score Football</p>
        <div data-widget-type="entityStandings" data-entity-type="league" data-entity-id="1" data-lang="id-id" data-widget-id="a89ad2ec-1eff-40b1-bd66-6c32fae065f2" data-limit-height-display="350" data-theme="dark"></div>
<div id="powered-by">Powered by<a id="powered-by-link" href="https://www.365scores.com" target="_blank">365Scores.com</a></div>
    </div>
    <div class="widget-item">
        <p class="widget-title">Live Score Basketball</p>
        <div data-widget-type="entityScores" data-entity-type="league" data-entity-id="103" data-lang="id-id" data-widget-id="77f26f17-0c2c-4286-a665-1465614ce556" data-limit-height-display="350" data-theme="dark"></div>
        <div id="powered-by">Powered by<a id="powered-by-link" href="https://www.365scores.com" target="_blank">365Scores.com</a></div>
    </div>
    <div class="widget-item">
        <p class="widget-title">Live Score Volleyball</p>
        <div data-widget-type="entityScores" data-entity-type="league" data-entity-id="6171" data-lang="id-id" data-widget-id="b6016234-86dd-4381-babe-ddeefdc9798d" data-limit-height-display="350" data-theme="dark"></div>
        <div id="powered-by">Powered by<a id="powered-by-link" href="https://www.365scores.com" target="_blank">365Scores.com</a></div>
    </div>
    <div class="widget-item">
        <p class="widget-title">Live Score Tenis</p>
        <div data-widget-type="entityScores" data-entity-type="league" data-entity-id="215" data-lang="id-id" data-widget-id="8b631bfa-d9d9-4729-88f8-983cf1c29935" data-limit-height-display="350" data-theme="dark"></div>
        <div id="powered-by">Powered by<a id="powered-by-link" href="https://www.365scores.com" target="_blank">365Scores.com</a></div>
    </div>
</div>

<script src="https://widgets.365scores.com/main.js"></script>