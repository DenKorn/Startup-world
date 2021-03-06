"use strict";

/**
 * Created by Денис on 23.12.2016.
 * Заранее прошу не пугаться кода. Да, здесь стоило и можно было применить ООП с паттернами "Фабрика", "Команда", "Заместитель" и прочие прелести.
 * Но в силу сжатых сроков архитектуру нормальную для фронтенда спланировать не вышло
 */
let messagingController = (function () {

    //для взаимодействия с сообщениями
    let lastInteractedMessageId = 0;
    let currentAction = 0; // 0 - send, 1 - edit (2 - delete - возможен только тогда же, когда возможен и edit)

    const NOTIFY_INFO = 0;
    const NOTIFY_WARNING = 1;

    const MODAL_ACTION_SEND = 0;
    const MODAL_ACTION_UPDATE = 1;
    const VOTING_FOLD = 1;

    const MODAL_MSG_INPUT_ELEMENT = document.querySelector('#respond-message');

    const msgContainer = document.querySelector('.messages-common-container');

    const modalFormHeader = document.querySelector('#myModal .modal-title');
    const modalRespondMessageField = document.querySelector('#respond-message');

    function getQueryLink(additionalSublink) {
        return window.API_BASE_LINK + '/discussions/' + additionalSublink;
    }

    /**
     * Удаляет класс из элемента, если он был присвоен ему
     *
     * @param arrowElement Object
     * @param classToRemove string
     */
    function removeClassIfContains(arrowElement, classToRemove) {
        if(arrowElement.classList.contains(classToRemove)) {
            arrowElement.classList.remove(classToRemove)
        }
    }

    /**
     * В зависимости от второго аргумента устанавливает либо сбрасывает стиль счетчика
     * голосов в определенном элементе в сообщении (элемент указываем в первом аргументе)
     *
     * @param voteCounterElement Object
     * @param changed boolean
     */
    function markVoteCounterAs(voteCounterElement, changed = true) {
        if(changed) {
            if(!voteCounterElement.classList.contains('rated-by-user')) {
                voteCounterElement.classList.add('rated-by-user');
            }
        } else {
            if(voteCounterElement.classList.contains('rated-by-user')) {
                voteCounterElement.classList.remove('rated-by-user');
            }
        }
    }

    /**
     * Обновляет содержимое контейнера .voting
     *
     * @param votingContainer Object
     * @param newValue integer
     * @param newRating integer
     */
    function setupVotingContainer(votingContainer, newValue, newRating) {
        const ratingCount =  votingContainer.children[0];
        const arrowUp = votingContainer.children[1];
        const arrowDown = votingContainer.children[2];

        if(ratingCount.innerHTML != newRating) {
            ratingCount.innerHTML = newRating;
        }

        //обновление состояния кнопок лайка/дизлайка
        switch (newValue) {
            case -VOTING_FOLD:
                removeClassIfContains(arrowUp,'triangle-up-selected');
                arrowDown.classList.add('triangle-down-selected');
                markVoteCounterAs(ratingCount, true);
                break;
            case +VOTING_FOLD:
                removeClassIfContains(arrowDown,'triangle-down-selected');
                arrowUp.classList.add('triangle-up-selected');
                markVoteCounterAs(ratingCount, true);
                break;
            default:
                markVoteCounterAs(ratingCount, false);
                removeClassIfContains(arrowUp,'triangle-up-selected');
                removeClassIfContains(arrowDown,'triangle-down-selected');
        }
    }

    /**
     * функция отправки запроса на сервер для установки рейтинга сообщения сообщения (оценка +1 или -1 в рейтинг)
     *
     * @param id integer
     * @param direction string
     */
    function vote(id, direction) {

        let requestObj = {
            msg_id : id,
            value : null
        };

        switch(direction) {
            case 'up': requestObj.value = +VOTING_FOLD;
                break;
            case 'down': requestObj.value = -VOTING_FOLD;
                break;
            default: console.error('Некорректный параметр оценки: '+direction+'. Допустимы лишь варианты up и down');
        }

        $.get(getQueryLink("update-voting"),requestObj)
            .done((respond)=>{
                switch (respond.result) {
                    case 'ok':
                        userNotifications.notify(respond.message, NOTIFY_INFO);
                        setupVotingContainer(
                            document.querySelector(`#message-${id} .voting`),
                            respond.newVote ? +respond.newVote : 0,
                            respond.currentRating ? +respond.currentRating : 0
                        );
                        break;
                    case 'error':
                        userNotifications.notify(respond.message, NOTIFY_WARNING);
                        if(respond.data) {
                            console.error(respond.data);
                        }
                        break;
                    default:
                        console.error(`Непредвиденный результат ответа от сервера: "${respond}"`);
                }
            })
            .fail((error)=>{console.log("message updating error: ",error);
                userNotifications.notify("Не удалось изменить рейтинг сообщения",NOTIFY_WARNING)});
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
            `<a onclick="messagingController.expandBranch(${element.msg_id})" class="btn btn-default message-expand-btn btn-xs">Развернуть <span class="badge">${element.subjected_count}</span></a>`
            : '';

        //добавление кнопки "редактировать"
        let edit_btn = !window.CLIENT_BANNED && window.CLIENT_ID && element.editable ?
            `<a onclick="messagingController.prepareModal(${element.msg_id},${MODAL_ACTION_UPDATE})" class="btn btn-default btn-xs" data-toggle="modal" data-target="#myModal">Редактировать</a>`
            : '';

        //добавление кнопки "удалить"
        let delete_btn = !window.CLIENT_BANNED && window.CLIENT_ID && element.editable ?
            `<a onclick="messagingController.deleteMessage(${element.msg_id})" class="btn btn-default btn-xs">Удалить</a>`
            : '';

        //добавление кнопки "ответить"
        let respond_btn = !window.CLIENT_BANNED && window.CLIENT_ID ?
            `<a onclick="messagingController.prepareModal(${element.msg_id},${MODAL_ACTION_SEND})" class="btn btn-default btn-xs" data-toggle="modal" data-target="#myModal">Ответить</a>`
            : '';

        let voting_block = !window.CLIENT_BANNED && window.CLIENT_ID && (element.rating || element.rating === 0) ? `<div class="voting">
         <span class="rate${element.voting_choise == 0 ? '' : ' rated-by-user'}">${element.rating}</span>
         <div onclick="messagingController.vote(${element.msg_id}, 'up')" class="triangle-up${element.voting_choise == VOTING_FOLD ? ' triangle-up-selected ' : ''}"></div> 
         <div onclick="messagingController.vote(${element.msg_id}, 'down')" class="triangle-down${element.voting_choise == -VOTING_FOLD ? ' triangle-down-selected ' : ''}"></div>
         </div>` : '';

        messageContainer.innerHTML = `
        <div class="message-block ${ (window.CLIENT_ID && element.user_id == window.CLIENT_ID) ? "message-own" : ""}" id="message-${element.msg_id}">
            <div class="message-header">
                <a href="profile?id=${element.user_id}" class="message-user">@${element.user_login}</a>
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

    /**
     * функция подгрузки через AJAX части переписки:
     * инициируем подгрузку фрагмента JSON
     * по завершению выдаем либо уведомление об ошибке, либо сериализуем JSON и рендерим
     *
     * @param rootNodeId integer
     */
    function expandBranch(rootNodeId) {
        $.get(getQueryLink("ajax-load-branch"),{id:rootNodeId})
            .done(
                (tree)=>{
                    renderMsgTree(tree, (rootNodeId === window.ROOT_MSG_ID) ? undefined : document.querySelector(`#message-${rootNodeId}`));
                    let expBtnElem = document.querySelector(`#message-${rootNodeId} .message-expand-btn`);
                    if(expBtnElem) expBtnElem.remove();
                    userNotifications.notify('Фрагмент ветки от '+tree.user_login+' загружен',NOTIFY_INFO);
                })
            .fail(
                (error)=>{
                    console.log(error);
                    userNotifications.notify('Не удалось загрузить фрагмент',NOTIFY_WARNING);
                });
    }

    /**
     * Вызывается для инициализации глобальных переменных перед вызовом модального окна
     * @param usedMessageId string
     * @param action string
     * @param author_login string
     */
    function prepareModal(usedMessageId, action = MODAL_ACTION_SEND, author_login = null) {
        lastInteractedMessageId = usedMessageId;
        currentAction = action;
        switch (action) {
            case MODAL_ACTION_SEND : {
                if(author_login) {
                    modalFormHeader.innerHTML = 'Ответить пользователю ' + author_login;
                } else {
                    let userNameBlock = document.querySelector(`#message-${usedMessageId} .message-user`);
                    let username = userNameBlock ? userNameBlock.innerHTML : '@not_found';
                    modalFormHeader.innerHTML = 'Ответить пользователю ' + username;
                }
                break;
            }
            case MODAL_ACTION_UPDATE : {
                modalFormHeader.innerHTML = `Редактировать своё сообщение`;
                MODAL_MSG_INPUT_ELEMENT.value = document.querySelector(`#message-${usedMessageId} .message-text`).innerHTML;
                break;
            }
        }
        modalRespondMessageField.focus(); //установим фокус на поле ввода открывающейся модальной формы
    }

    function deleteMessage(msg_id) {
        $.get(getQueryLink('delete-message'),{id : msg_id})
            .done((respond)=>{
                if(respond.result === 'ok') {
                    document.querySelector('#message-'+msg_id).remove();
                    userNotifications.notify(`Сообщение с подчиненной ему веткой удалено.`,NOTIFY_INFO);
                } else if(respond.result == 'error') {
                    userNotifications.notify(respond.message,NOTIFY_WARNING);
                }
            })
            .fail((error)=>{
                console.log('Deleting error',error);
                userNotifications.notify('Не удалось удалить сообщение.',NOTIFY_WARNING);
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
        $.get(getQueryLink("update-message"),{id: msg_id, content:content})
            .done((respond)=>{
                if(respond.result === 'ok') {
                    let targetMsgContainer = document.querySelector(`#message-${msg_id} .message-text`);
                    if(targetMsgContainer) {
                        targetMsgContainer.innerHTML = respond.new_content;
                        userNotifications.notify(`Сообщение отправлено.`);
                    } else {
                        userNotifications.notify('Ошибка выполнения: не найден элемент для обновления (настучите админу по голове, чтобы передавал в ответе на запрос, на всякий случай, целый обьект обновленного сообщения).',NOTIFY_WARNING);
                    }
                } else if(respond.result = 'error') {
                    userNotifications.notify(respond.message,NOTIFY_WARNING);
                }
            })
            .fail((error)=>{console.log("message updating error: ",error);
                userNotifications.notify("Не удалось обновить сообщение",NOTIFY_WARNING)});
    }

    /**
     * запрос отправки сообщения
     * должен быть получен id для присваивания
     * иначе выдать сообщение об ошибке
     * @param respond_to
     * @param content
     */
    function sendMessage(respond_to,content) {
        $.get(getQueryLink("create-message"),{respond_to:respond_to, content:content})
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
                    userNotifications.notify(`Сообщение отправлено.`)
                } else if(respond.result = 'error') {
                    userNotifications.notify(respond.message,NOTIFY_WARNING);
                }
            })
            .fail((error)=>{
                console.log("message sending error: ",error);
                userNotifications.notify("Не удалось отправить сообщение",NOTIFY_WARNING)
            });


    }

    /**
     * функция отправки сообщения по нажатию кнопки "отправить" в модальной форме
     * id сообщения для взаимодействия и действие берет из глобальных переменных
     */
    function sendModal() {
        let errorLength = (action) => {
            userNotifications.notify(`Не удалось ${action} сообщение: превышена допустимая длина.`,NOTIFY_WARNING)
        };

        let errorLengthSmall = (action) => {
            userNotifications.notify(`Cообщение не ${action}: сообщение пусто.`,NOTIFY_WARNING)
        };

        const MAX_MSG_LENGTH = 1500;
        const MIN_MSG_LENGTH = 1;
        let inputValue = modalRespondMessageField.value;
        switch (currentAction) {
            case MODAL_ACTION_SEND : { //отправка сообщения
                if(inputValue.length <= MAX_MSG_LENGTH) {
                    if(inputValue.length >= MIN_MSG_LENGTH) {
                        sendMessage(lastInteractedMessageId,inputValue);
                    } else errorLengthSmall('отправлено');
                } else errorLength('отправить');
                break;
            }
            case MODAL_ACTION_UPDATE : { //обновление сообщения
                if(inputValue.length <= MAX_MSG_LENGTH) {
                    if(inputValue.length >= MIN_MSG_LENGTH) {
                        if(document.querySelector(`#message-${lastInteractedMessageId} .message-text`).innerHTML != inputValue) {
                            updateMessage(lastInteractedMessageId,inputValue);
                        } else {
                            userNotifications.notify('Вы не изменили сообщение.',NOTIFY_INFO);
                        }
                    } else errorLengthSmall('обновлено');
                } else errorLength('обновить');
                break;
            }
            default: console.log('В функции sendModal попытка вызова неопознанного действия(из величины currentAction)');
        }
        //очистка формы через время после её скрытия
        setTimeout(function(){
            document.querySelector('#respond-message').value=''
        },500);
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

    /**
     * Начальные настройки модуля. Если USER_ID не определен (либо эта настройка выключена) - личные сообщения пользователя не подсвечиваются
     * todo много чего...
     */
    function initModule() {

    }

    window.addEventListener('load', ()=>{init()});

    return {
        expandBranch : expandBranch,
        vote : vote,
        prepareModal: prepareModal,
        sendModal : sendModal,
        deleteMessage: deleteMessage
    }
})();