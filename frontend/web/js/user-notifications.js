'use strict';

/**
 * Модуль отвечает за вывод всплывающих сообщений в правой стороне страниц на сайте. Должен работать только в случае, если
 * пользователь аутентифицирован. Также модуль регулярно опрашивает сервер на предмет уведомлений пользователю, и при этом
 * обновляет пользовательский статус online
 *
 * @type {{notify, init}}
 */
let userNotifications = (function () {

    let lastUsedNotifyId = 0;

    /**
     * При опросе сервера ответ на запрос (a) может приходить позже, чем отправлен запрос (а+1), к примеру.
     * Поэтому ведем учет идентификаторов запросов и ответов. В случае необходимости уменьшаем частоту запросов
     *
     * @type {number}
     */
    let lastRequestId = 0;
    let lastResponseId = 0;

    /**
     * функция добавления уведомления (также идет вывод в консоль)
     * type принимает значения 0 (простое уведомление), или 1 (уведомление об ошибке)
     *
     * @param message string
     * @param type int {{0, 1}}
     */
    function notify(message, type = 0) {
        let id = lastUsedNotifyId++;
        document.querySelector('.forum-alerts').innerHTML +=
            `<div class="alert ${type == 0 ? 'alert-info' : 'alert-warning'} alert-dismissable fade">
             <a href="#" id="notify-close-btn-${id}" class="close" data-dismiss="alert" aria-label="close">×</a>
             <strong>${type == 0 ? 'Info' : 'Warning'}:</strong> ${message}
            </div>`;
        setTimeout(()=>{document.querySelector('#notify-close-btn-' + id).parentNode.classList.add('in')},100);
        setTimeout(()=>{document.querySelector('#notify-close-btn-' + id).click()},5000);
        switch (type) {
            case 0: console.info(message); break;
            case 1: console.warn(message); break;
            default: console.log(message); break;
        }
    }

    function askForUserNotifications() {

    }

    /**
     * Инициализация модуля с запуском интервального таймера для опроса сервера, на основе обьекта параметров
     * перед вызовом в настройки добавить адрес сервера и user_id (или null, если пользователь не авторизован)
     *
     * @param parameters object
     */
    function init(parameters) {
        //если пользователь залогинен и получили его ID, то запускаем интервальную функцию
    }

    return {
        notify: notify,
        init: init
    }
})();