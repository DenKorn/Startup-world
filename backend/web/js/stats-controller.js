/**
 * Created by korne on 12.03.2017.
 * Загружает скрипт google charts, а также загружает с сервера нужные для отображения статистики данные в виде JSON-обьекта
 */

let statsController = (function () {

    /**
     * Функция получения метки текущего времени. Взята из underscore.js для экономии кода
     * @type {*}
     */
    let getNow = Date.now || function() {
        return new Date().getTime();
    };

    /**
     * Функция-декоратор для ограничения количества вызовов функции-аргумента. Функция оказывается доступна к вызову
     * лишь спустя определенный период, пока она не была вызвана (микропаттерн дебаунсинг). В данном коде используется
     * для ограничения срабатывания колбека при изменения размера формы.
     *
     * @param func function
     * @param wait integer
     * @param immediate boolean
     * @returns {Function}
     */
    function debounce (func, wait, immediate) {
        let timeout, args, context, timestamp, result;

        let later = function() {
            let last = getNow() - timestamp;

            if (last < wait && last >= 0) {
                timeout = setTimeout(later, wait - last);
            } else {
                timeout = null;
                if (!immediate) {
                    result = func.apply(context, args);
                    if (!timeout) context = args = null;
                }
            }
        };

        return function() {
            context = this;
            args = arguments;
            timestamp = getNow();
            let callNow = immediate && !timeout;
            if (!timeout) timeout = setTimeout(later, wait);
            if (callNow) {
                result = func.apply(context, args);
                context = args = null;
            }

            return result;
        };
    }

    /**
     * Коллбек выполняется при готовности модуля диаграмм. Запрашивает данные у сервера, и задает колбек
     * для перерисовки диаграммы при ресайзинге окна
     */
    function initChart() {
        let dataTable = null;

        let options = {
            hAxis: {
                title: 'Время (данные за последний год)'
            },
            /*vAxis: {
                title: 'Количество'
            },*/
            series: {
                1: {curveType: 'none'}
            },
            chartArea: {
                width: '90%',
                height: '60%'
            },
            legend: {
                position: 'top'
            },
            height: 400
        };

        let chart = new google.visualization.LineChart(document.getElementById('chart_div'));

        function drawDiagram() {
            if(dataTable) {
                chart.draw(dataTable, options);
            }
        }

        let xhr = new XMLHttpRequest();
        xhr.addEventListener('load', function (evt) {
            dataTable = google.visualization.arrayToDataTable(JSON.parse(evt.target.response));
            drawDiagram();
        });
        xhr.open('GET', window.API_BASE_LINK+'/site/get-forum-stats');
        xhr.send();

        window.onresize = debounce(drawDiagram, 300);
    }

    /**
     *
     * @param best boolean
     */
    function loadMostOutstandingUsers(best, renderContainerId) {
        //class success or danger
        let xhr = new XMLHttpRequest();
        xhr.addEventListener('load', function (evt) {
            let result = JSON.parse(evt.target.response);
            let profileLink = window.API_BASE_LINK.replace("admin","profile?id=");

            let tBody = document.getElementById(renderContainerId);
            let tContainer = tBody.parentNode;
            if(result.length > 0) {
                let tHead = document.createElement('thead');
                tHead.innerHTML = `<tr><th>№</th><th>id</th><th>Общий рейтинг</th><th>Ссылка</th></tr>`;
                tContainer.insertBefore(tHead,tBody);
            }
            result.forEach((item, index)=>{
                tBody.innerHTML += `
                    <tr class="${best ? 'success' : 'danger'}">
                        <td>${index+1}</td>
                        <td>${item.user_id}</td>
                        <td>${item.sum_rating}</td>
                        <td><a href="${profileLink+item.user_id}">профиль</a></td>
                    </tr>`;
            });
        });
        xhr.open('GET', window.API_BASE_LINK+'/site/get-outstanding-users?best_users='+best);
        xhr.send();
    }
    
    
    google.charts.load('current', {packages: ['corechart', 'line']});
    google.charts.setOnLoadCallback(initChart);
    loadMostOutstandingUsers(true, 'most_rated_users_table_container');
    loadMostOutstandingUsers(false, 'less_rated_users_table_container');
})();
