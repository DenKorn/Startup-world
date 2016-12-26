/**
 * Created by Денис on 23.12.2016.
 * Заранее прошу не пугаться кода. Да, здесь стоило и можно было применить ООП с паттернами "Фабрика", "Команда", "Заместитель" и прочие прелести.
 * Но в силу сжатых сроков архитектуру нормальную для фронтенда спланировать не вышло
 */

//для присваивания id к каждому создаваемому обьекту уведомления
lastUsedNotifyId = 0;

//for messages interacting
let lastInteractedMessageId = 0;
let currentAction = 0; // 0 - send, 1 - edit (2 - delete - возможен только тогда же, когда возможен и edit)

const NOTIFY_INFO = 0;
const NOTIFY_WARNING = 1;

const MODAL_ACTION_SEND = 0;
const MODAL_ACTION_UPDATE = 1;


const msgContainer = document.querySelector('.messages-common-container');

/**
 * функция отправки запроса на сервер для лайка сообщения (оценка +1 в рейтинг)
 * @param id
 */
function voteUp(id) {
    let requestObj = {
        msg_id : id,
        action : true ? 'set' : 'cancel'
    };
    if(requestObj.action === 'set') requestObj.value = +1;
    $.get(window.API_BASE_LINK+"update-voting",requestObj)
        .done((respond)=>{
            if(respond.result = 'ok') {
                let targetMsgContainer = document.querySelector(`#message-${msg_id} .message-text`);
                if(targetMsgContainer) {
                    targetMsgContainer.innerHTML = respond.new_content;
                    notify(`Сообщение отправлено.`);
                } else {
                    notify('Ошибка выполнения: не найден элемент для обновления (настучите админу по голове, чтобы передавал в ответе на запрос, на всякий случай, целый обьект обновленного сообщения).',NOTIFY_WARNING);
                }
            } else if(respond.result = 'error') {
                notify(respond.message,NOTIFY_WARNING);
            }
        })
        .fail((error)=>{console.log("message updating error: ",error);
            notify("Не удалось обновить сообщение",NOTIFY_WARNING)});



    //done:
    notify(`Вы подняли рейтинг сообщения`,NOTIFY_INFO);
    notify(`Голос отменён`,NOTIFY_INFO);
    //fail:
    notify(`Вы уже поднимали рейтинг этого сообщения!`,NOTIFY_WARNING);
}

/**
 * функция отправки запроса на сервер для дизлайка сообщения (оценка -1 в рейтинг)
 * @param id
 */
function voteDown(id) {
    let requestObj = {
        msg_id : id,
        action : true ? 'set' : 'cancel'
    };
    if(requestObj.action === 'set') requestObj.value = -1;
    //todo дизлайк сообщению
    //тоже аякс-запрос
    //done:
    notify(`Вы понизили рейтинг сообщения`,NOTIFY_INFO);
    notify(`Голос отменён`,NOTIFY_INFO);
    //fail:
    notify(`Вы уже понижали рейтинг этого сообщения!`,NOTIFY_WARNING)
}

/**
 * возвращает обьект сформированного DOM-блока с сообщением
 * @param element
 * @returns {Element}
 */
function renderElement(element) {
    let messageContainer = document.createElement('div');
    messageContainer.className = "message-container";

    //дообавление кнопки "раскрыть ветку"
    let open_branch = (element.subjected.length === 0 && element.subjected_count > 0) ?
        `<a onclick="expandBranch(${element.msg_id})" class="btn btn-default message-expand-btn btn-xs">Открыть(${element.subjected_count})</a>`
        : '';

    //добавление кнопки "редактировать"
    let edit_btn = window.CLIENT_ID && element.editable ?
        `<a onclick="prepareModal(${element.msg_id},MODAL_ACTION_UPDATE)" class="btn btn-default btn-xs" data-toggle="modal" data-target="#myModal">Редактировать</a>`
        : '';

    //добавление кнопки "удалить"
    let delete_btn = window.CLIENT_ID && element.editable ?
        `<a onclick="deleteMessage(${element.msg_id})" class="btn btn-default btn-xs">Удалить</a>`
        : '';

    //добавление кнопки "ответить"
    let respond_btn = window.CLIENT_ID ?
        `<a onclick="prepareModal(${element.msg_id},MODAL_ACTION_SEND)" class="btn btn-default btn-xs" data-toggle="modal" data-target="#myModal">Ответить</a>`
        : '';

    let voting_block = window.CLIENT_ID && (element.rating || element.rating === 0) ? `<div class="voting">
         <span class="rate">${element.rating}</span>
         <div onclick="voteUp(${element.msg_id})" class="triangle-up"></div>
         <div onclick="voteDown(${element.msg_id})" class="triangle-down"></div>
         </div>` : '';

    messageContainer.innerHTML = `
        <div class="message-block" id="message-${element.msg_id}">
            <div class="message-header">
                <div class="message-user">@${element.user_login}</div>
                <div class="message-created-time">${element.created_at}</div>
                ${voting_block}
            </div>
            <div class="message-text">${element.content}</div>
            ${respond_btn}
            ${edit_btn}
            ${delete_btn}
            ${open_branch}
        </div>`;
    return messageContainer;
}

/**
 * отрисовка загруженного дерева сообщений
 * @param subtreeElement
 * @param parentNode
 */
function renderMsgTree(subtreeElement, parentNode = msgContainer) {
    parentNode = parentNode || msgContainer;
    subtreeElement.subjected.forEach((element)=>{
        let container = renderElement(element,parentNode);
        renderMsgTree(element,container );
        parentNode.appendChild(container);
    });
}

// функция подгрузки через AJAX части переписки
//инициируем подгрузку фрагмента JSON
// По завершению выдаем либо уведомление об ошибке, либо сериализуем JSON и рендерим
function expandBranch(rootNodeId, branchLevels=1) {
    $.get(window.API_BASE_LINK+"ajax-load-branch",{id:rootNodeId}) //убран levels:branchLevels, величина будет браться из сервера
        .done(
            (tree)=>{
                console.log(tree);
                renderMsgTree(tree, (rootNodeId === window.ROOT_MSG_ID) ? undefined : document.querySelector(`#message-${rootNodeId}`));
                let expBtnElem = document.querySelector(`#message-${rootNodeId} .message-expand-btn`);
                if(expBtnElem) expBtnElem.remove();
                notify('Фрагмент ветки от '+tree.user_login+' загружен',NOTIFY_INFO);
            })
        .fail(
            (error)=>{
                console.log(error);
                notify('Не удалось загрузить фрагмент',NOTIFY_WARNING);
            });
}

/**
 * Вызывается для инициализации глобальных переменных перед вызовом модального окна
 * @param usedMessageId
 * @param action
 */
function prepareModal(usedMessageId, action = MODAL_ACTION_SEND) {
    lastInteractedMessageId = usedMessageId;
    currentAction = action;
    switch (action) {
        case MODAL_ACTION_SEND : {
            let userNameBlock = document.querySelector(`#message-${usedMessageId} .message-user`);
            let username = userNameBlock ? userNameBlock.innerHTML : '@not_found';
            document.querySelector('#myModal .modal-title').innerHTML = 'Ответить пользователю ' + username;
            break;
        }
        case MODAL_ACTION_UPDATE : {
            document.querySelector('#myModal .modal-title').innerHTML = `Редактировать своё сообщение`;
            break;
        }
    }
}

function deleteMessage(msg_id) {
    $.get(window.API_BASE_LINK+'delete-message',{id : msg_id})
        .done((respond)=>{
            if(respond.result === 'ok') {
                document.querySelector('#message-'+msg_id).remove();
                notify(`Сообщение с подчиненной ему веткой удалено.`,NOTIFY_INFO);
            } else if(respond.result == 'error') {
                notify(respond.message,NOTIFY_WARNING);
            }
        })
        .fail((error)=>{
            console.log('Deleting error',error);
            notify('Не удалось удалить сообщение.',NOTIFY_WARNING);
        });
}

/**
 * запрос обновления содержимого сообщения
 * ожидается логический ответ
 * если всё норм, то присваиваем полю новое сообщение и очищаем модальную форму, и уведомление об успехе
 * иначе сообщение об ошибке
 * @param msg_id
 * @param content
 */
function updateMessage(msg_id,content) {
    $.get(window.API_BASE_LINK+"update-message",{id: msg_id, content:content})
        .done((respond)=>{
            if(respond.result === 'ok') {
                let targetMsgContainer = document.querySelector(`#message-${msg_id} .message-text`);
                if(targetMsgContainer) {
                    targetMsgContainer.innerHTML = respond.new_content;
                    notify(`Сообщение отправлено.`);
                } else {
                    notify('Ошибка выполнения: не найден элемент для обновления (настучите админу по голове, чтобы передавал в ответе на запрос, на всякий случай, целый обьект обновленного сообщения).',NOTIFY_WARNING);
                }
            } else if(respond.result = 'error') {
                notify(respond.message,NOTIFY_WARNING);
            }
        })
        .fail((error)=>{console.log("message updating error: ",error);
            notify("Не удалось обновить сообщение",NOTIFY_WARNING)});
}

/**
 * запрос отправки сообщения
 * должен быть получен id для присваивания
 * иначе выдать сообщение об ошибке
 * @param respond_to
 * @param content
 */
function sendMessage(respond_to,content) {
    $.get(window.API_BASE_LINK+"create-message",{respond_to:respond_to, content:content})
        .done((respond)=>{
            if(respond.result === 'ok') {
                let msgElement = renderElement({
                    user_id : window.CLIENT_ID,
                    user_login : window.CLIENT_LOGIN,
                    user_role : window.CLIENT_ROLE,
                    rating : 0,
                    voting_choise : 0,
                    editable : true,
                    msg_id : respond.new_msg_id,
                    created_at : respond.created_at ? respond.created_at : 'только что',
                    content : respond.msg_content,
                    subjected_count : 0,
                    subjected : []
                });
                let targetContainer = document.querySelector('#message-'+respond_to);
                targetContainer = targetContainer ? targetContainer : msgContainer;
                targetContainer.appendChild(msgElement);
                notify(`Сообщение отправлено.`)
            } else if(respond.result = 'error') {
                notify(respond.message,NOTIFY_WARNING);
            }
        })
        .fail((error)=>{
            console.log("message sending error: ",error);
            notify("Не удалось отправить сообщение",NOTIFY_WARNING)
        });


}

/**
 * функция отправки сообщения по нажатию кнопки "отправить" в модальной форме
 * id сообщения для взаимодействия и действие берет из глобальных переменных
 */
function sendModal() {
    let errorLength = (action) => {notify(`Не удалось ${action} сообщение: превышена допустимая длина.`,NOTIFY_WARNING)};
    let errorLengthSmall = (action) => {notify(`Cообщение не ${action}: сообщение пусто.`,NOTIFY_WARNING)};
    const MAX_MSG_LENGTH = 1500;
    const MIN_MSG_LENGTH = 1;
    let inputValue = document.querySelector('#respond-message').value;
    switch (currentAction) {
        case MODAL_ACTION_SEND : {
            if(inputValue.length <= MAX_MSG_LENGTH) { //отправка сообщения
                if(inputValue.length >= MIN_MSG_LENGTH) {
                    sendMessage(lastInteractedMessageId,inputValue);
                } else errorLengthSmall('отправлено');
            } else errorLength('отправить');
            break;
        }
        case MODAL_ACTION_UPDATE : { //обновление сообщения
            if(inputValue.length <= MAX_MSG_LENGTH) {
                if(inputValue.length >= MIN_MSG_LENGTH) {
                    updateMessage(lastInteractedMessageId,inputValue);
                } else errorLengthSmall('обновлено');
            } else errorLength('обновить');
            break;
        }
        default: console.log('В функции sendModal попытка вызова неопознанного действия(из величины currentAction)');
    }
    //очистка формы через время
    setTimeout(function(){document.querySelector('#respond-message').value=''},500);
}

//функция добавления уведомления
function notify(message, type = 0) { //0 - info, 1 - warning
    let id = lastUsedNotifyId++;
    document.querySelector('.forum-alerts').innerHTML +=
            `<div class="alert ${type == 0 ? 'alert-info' : 'alert-warning'} alert-dismissable fade">
             <a href="#" id="notify-close-btn-${id}" class="close" data-dismiss="alert" aria-label="close">×</a>
             <strong>${type == 0 ? 'Info' : 'Warning'}:</strong> ${message}
            </div>`;
    setTimeout(()=>{document.querySelector('#notify-close-btn-'+id).parentNode.classList.add('in')},100);
    setTimeout(()=>{document.querySelector('#notify-close-btn-'+id).click()},5000);
}

function init() {
    window.sendModal = sendModal;
    let bodyElement = document.querySelector('body');
    let sendBtn = document.querySelector('#myModal .send-btn');
    let closeBtn = document.querySelector('#myModal .close-btn');
    document.body.onkeyup = (event) => {
        if(bodyElement.classList.contains('modal-open')) {
            switch (event.keyCode) {
                case 13 : { //ENTER BUTTON
                    sendBtn.click();
                    break;
                }
                case 27 : { //ESCAPE BUTTON
                    closeBtn.click();
                    break;
                }
            }
        }
    };
}

window.addEventListener('load', ()=>{init()});