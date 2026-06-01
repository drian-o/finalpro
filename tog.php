<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Uji Coba Widget Live Score</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; margin: 0; padding: 20px; background-color: #1a1a1a; color: #fff; }
        .widget-title { text-align: center; font-size: 1.5rem; font-weight: bold; margin-bottom: 20px; }
        .horizontal-scroll-container {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 15px;
        }
        .widget-card {
            flex: 0 0 auto;
            width: 300px;
            background-color: #282c34;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .widget-card .title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #ff9900;
            margin-bottom: 10px;
        }
        .widget-card .widget-content {
            min-height: 350px;
        }
    </style>
</head>
<body>

    <h2 class="widget-title">Uji Coba Tampilan Widget Live Score</h2>
    
    <div class="horizontal-scroll-container">
        
        <div class="widget-card">
            <p class="title">Live Score Football</p>
            <div class="widget-content">
                <div data-widget-type="entityScores" data-entity-type="league" data-entity-id="10" data-lang="id-id" data-widget-id="1d88c4af-d691-4e2c-a4d0-8f11afe4e363" data-limit-height-display="350" data-theme="dark"></div>
                <div id="powered-by">Powered by<a id="powered-by-link" href="https://www.365scores.com" target="_blank">365Scores.com</a></div>
            </div>
        </div>

        <div class="widget-card">
            <p class="title">Live Score Basketball</p>
            <div class="widget-content">
                <div data-widget-type="entityScores" data-entity-type="league" data-entity-id="103" data-lang="id-id" data-widget-id="77f26f17-0c2c-4286-a665-1465614ce556" data-limit-height-display="350" data-theme="dark"></div>
                <div id="powered-by">Powered by<a id="powered-by-link" href="https://www.365scores.com" target="_blank">365Scores.com</a></div>
            </div>
        </div>

        <div class="widget-card">
            <p class="title">Live Score Volleyball</p>
            <div class="widget-content">
                <div data-widget-type="entityScores" data-entity-type="league" data-entity-id="6171" data-lang="id-id" data-widget-id="b6016234-86dd-4381-babe-ddeefdc9798d" data-limit-height-display="350" data-theme="dark"></div>
                <div id="powered-by">Powered by<a id="powered-by-link" href="https://www.365scores.com" target="_blank">365Scores.com</a></div>
            </div>
        </div>

        <div class="widget-card">
            <p class="title">Live Score Tenis</p>
            <div class="widget-content">
                <div data-widget-type="entityScores" data-entity-type="league" data-entity-id="215" data-lang="id-id" data-widget-id="8b631bfa-d9d9-4729-88f8-983cf1c29935" data-limit-height-display="350" data-theme="dark"></div>
                <div id="powered-by">Powered by<a id="powered-by-link" href="https://www.365scores.com" target="_blank">365Scores.com</a></div>
            </div>
        </div>

    </div>

    <script src="https://widgets.365scores.com/main.js"></script>

</body>
</html>