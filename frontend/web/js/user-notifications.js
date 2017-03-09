'use strict';

/**
 * Модуль отвечает за вывод всплывающих сообщений в правой стороне страниц на сайте. Должен работать только в случае, если
 * пользователь аутентифицирован. Также модуль регулярно опрашивает сервер на предмет уведомлений пользователю, и при этом
 * обновляет пользовательский статус online
 *
 * @type {{notify, init}}
 */
let userNotifications = (function () {

    const NOTIFY_INFO = 0;
    const NOTIFY_WARNING = 1;

    let lastUsedNotifyId = 0;

    /**
     * функция добавления уведомления (также идет вывод в консоль)
     * type принимает значения 0 (простое уведомление), или 1 (уведомление об ошибке)
     *
     * @param message string
     * @param type int {{0, 1}}
     */
    function notify(message, type = NOTIFY_INFO) {
        let id = lastUsedNotifyId++;
        document.querySelector('.forum-alerts').innerHTML +=
            `<div class="alert ${type == NOTIFY_INFO ? 'alert-info' : 'alert-warning'} alert-dismissable fade">
             <a href="#" id="notify-close-btn-${id}" class="close" data-dismiss="alert" aria-label="close">×</a>
             <strong>${type == NOTIFY_INFO ? 'Info' : 'Warning'}:</strong> ${message}
            </div>`;
        setTimeout(()=>{
            let notificationNode = document.querySelector('#notify-close-btn-' + id).parentNode.classList;
            if(notificationNode) {
                notificationNode.add('in');
            }
        },100);
        setTimeout(()=>{
            let notificationNode = document.querySelector('#notify-close-btn-' + id);
            if(notificationNode) {
                notificationNode.click()
            }
        },5000);

        switch (type) {
            case NOTIFY_INFO: console.info(message); break;
            case NOTIFY_WARNING: console.warn(message); break;
            default: console.log(message); break;
        }
    }

    function showNotifications(notifications, notificationType = NOTIFY_INFO) {
        notifications.forEach((item) => {
            notify(item, notificationType);
        });
    }

    function processSystemNotifications(notifications) {
        notifications.forEach((item) => {
            let itemObj = JSON.parse(item);
            switch (itemObj.command) {
                case 'reload': setTimeout(()=> { location.reload() }, itemObj.parameter);
                    break;
                //todo добавить остальные возможные системные сообщения
                default: console.error('Неизвестное системное уведомление от сервера: '+item);
            }
        });
    }

    function AJAXCallback(evt) {
        let respond = JSON.parse(evt.target.responseText);
        if(!respond.result) return;

        switch (respond.result) {
            case 'ok':
                if(respond.system && respond.system.length > 0) processSystemNotifications(respond.system);
                if(respond.alerts && respond.alerts.length > 0) showNotifications(respond.alerts, NOTIFY_INFO);
                if(respond.warnings && respond.warnings.length > 0) showNotifications(respond.warnings, NOTIFY_WARNING);
                break;
            case 'error':
                notify(respond.message, NOTIFY_WARNING);
                break;
        }
    }

    function askForUserNotifications() {
        let xhr = new XMLHttpRequest();
        xhr.addEventListener('load', AJAXCallback);
        xhr.open('GET', window.API_BASE_LINK+'/site/check-notifications');
        xhr.send();
    }

    /**
     * Инициализация модуля с запуском интервального таймера для опроса сервера, на основе обьекта параметров
     * перед вызовом в настройки добавить адрес сервера и user_id (или null, если пользователь не авторизован)
     * requestInterval означает интервал опроса сервера об уведомлениях, в секундах
     *
     * @param requestInterval integer
     */
    function init(requestInterval) {
        // если пользователь залогинен и получили его ID,  то запускаем интервальную функцию опроса сервера, после получения предварительных настроек
        if(window.CLIENT_ID) {
            setInterval(askForUserNotifications, requestInterval * 1000);
        } else if(window.CLIENT_ID === undefined) {
            console.error('Не удалось инициализировать таймер запросов к серверу для опроса');
        }
    }

    return {
        notify: notify,
        init: init
    }
})();