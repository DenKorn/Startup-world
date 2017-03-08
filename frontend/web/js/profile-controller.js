/**
 * Обслуживает страницу профиля для выполнения запросов серверу
 * Из-за сжатых сроков выполнения была написана упрощенная версия, без предложений ввести ещё незаполненные поля, и не предлагать позднее
 *
 * @type {{}}
 */

let profileController = (function () {

    const NOTIFY_INFO = 0;
    const NOTIFY_WARNING = 1;

    /**
     * Также кешируем по ходу выполнения ссылки на form_groups и поля ввода
     */
    let fields = {};

    /**
     * Статус поля ввода может визуально определяться цветовой подсветкой, для этого нужно добавлять/удалять в главном контейнере поля ввода
     * классы .has-error и .has-success
     *
     * @param field Object
     * @param status string {{'none'|'error'|'success'}}
     */
    function toggleFieldStatus(field, status = 'none') {
        switch (status) {
            case 'success':
                if(field.classList.contains('has-error')) {
                    field.classList.remove('has-error');
                }
                if(!field.classList.contains('has-success')) {
                    field.classList.add('has-success');
                }
                break;
            case 'error':
                if(field.classList.contains('has-success')) {
                    field.classList.remove('has-success');
                }
                if(!field.classList.contains('has-error')) {
                    field.classList.add('has-error');
                }
                break;
            case 'none':
            default:
                if(field.classList.contains('has-success')) {
                    field.classList.remove('has-success');
                }
                if(field.classList.contains('has-error')) {
                    field.classList.remove('has-error');
                }
        }
    }
    
    /**
     * Возвращает обьект-ссылку на нужный элемент поля ввода, предварительно его кешируя
     *
     * @param fieldId
     * @returns Object
     */
    function getFieldById(fieldId) {
        if(typeof fieldId !== 'string') return null;

        if(!fields[fieldId]) {
            let formGroup = document.querySelector(`#${fieldId}`);
            if(formGroup) {
                fields[fieldId] = {
                    formGroupLink : formGroup,
                    inputLink : formGroup.querySelector('input')
                };
            } else {
                return null;
            }
        }
        return fields[fieldId];
    }

    function getQueryLink(additionalSublink) {
        return window.API_BASE_LINK + '/profile/' + additionalSublink;
    }

    /**
     * Первый параметр для формирования верного URL запроса
     * второй - ссылка на обьект, содержащий поле ввода, из которого мы подтверждаем сохранение
     * третий обьект - новая величина, которая будет отправлена на сервер для текущего пользователя
     *
     * @param action string
     * @param currentField Object
     * @param newValue string
     * @param okCallback function
     * @param errorCallback function
     * @returns {null}
     */
    function performNewValueToServer(action, currentField, newValue, okCallback = null, errorCallback = null) {
        if(!action) return null;

        $.get(getQueryLink(action),{ newValue : newValue})
            .done((respond)=>{
                switch (respond.result) {
                    case 'ok':
                        userNotifications.notify(respond.message, NOTIFY_INFO);
                        if(okCallback) {
                            okCallback(respond,currentField,newValue);
                        }
                        break;
                    case 'error':
                        userNotifications.notify(respond.message, NOTIFY_WARNING);
                        if(respond.data) {
                            console.error(respond.data);
                        }
                        if(errorCallback) {
                            errorCallback(respond,currentField,newValue);
                        }
                        break;
                    default:
                        console.error(`Непредвиденный результат ответа от сервера: "${respond}"`);
                }
            })
            .fail((error)=>{console.log("message updating error: ",error);
                userNotifications.notify("Не выполнить запрос",NOTIFY_WARNING)});
    }

    /**
     * Выполняет запрос при подтверждении поля ввода с заданным id
     * Второй параметр добавил для поддержки валидации ввода нового пароля
     * Метод делает валидацию согласно id формы ввода, а затем выполняет запрос на сервер
     *
     * @param fieldId string
     * @param additionalFieldId string
     */
    function updateFieldById(fieldId, additionalFieldId = null) {
        let field = getFieldById(fieldId);
        
        switch (fieldId) {
            case 'user_login': {
                if(field.inputLink.value.length < 4) { //todo мин. и макс. размер нового логина подтягивать из настроек сервера
                    toggleFieldStatus(field.formGroupLink, 'error');
                    userNotifications.notify('Слишком короткий логин! Минимальная длина: 4 символа.', NOTIFY_WARNING);
                    break;
                }
                if(field.inputLink.value.length > 25) {
                    toggleFieldStatus(field.formGroupLink, 'error');
                    userNotifications.notify('Слишком длинный логин! Максимальная длина: 25 символов.', NOTIFY_WARNING);
                    break;
                }

                //todo добавить проверку корректности логина (лишь латинские буквы и цифры, не должен начинаться с числа)

                toggleFieldStatus(field.formGroupLink, 'success');
                performNewValueToServer('change-login', field, field.inputLink.value, function () {
                    location.reload();
                });

                break;
            }

            case 'user_first_name': {
                if(field.inputLink.value.length < 2) { //todo мин. и макс. размер нового имени подтягивать из настроек сервера
                    toggleFieldStatus(field.formGroupLink, 'error');
                    userNotifications.notify('Слишком короткое имя! Минимальная длина: 4 символа.', NOTIFY_WARNING);
                    break;
                }
                if(field.inputLink.value.length > 60) {
                    toggleFieldStatus(field.formGroupLink, 'error');
                    userNotifications.notify('Слишком длинное имя! Максимальная длина: 60 символов.', NOTIFY_WARNING);
                    break;
                }

                //todo добавить проверку корректности имени

                toggleFieldStatus(field.formGroupLink, 'success');
                performNewValueToServer('change-first-name', field, field.inputLink.value);

                break;
            }

            case 'user_second_name': {
                if(field.inputLink.value.length < 2) { //todo мин. и макс. размер новой фамилии подтягивать из настроек сервера
                    toggleFieldStatus(field.formGroupLink, 'error');
                    userNotifications.notify('Слишком короткая фамилия! Минимальная длина: 4 символа.', NOTIFY_WARNING);
                    break;
                }
                if(field.inputLink.value.length > 60) {
                    toggleFieldStatus(field.formGroupLink, 'error');
                    userNotifications.notify('Слишком длинная! Максимальная длина: 60 символов.', NOTIFY_WARNING);
                    break;
                }

                //todo добавить проверку корректности имени

                toggleFieldStatus(field.formGroupLink, 'success');
                performNewValueToServer('change-second-name', field, field.inputLink.value);

                break;
            }

            case 'user_email': { //todo сделать так, чтобы при HTML-валидации красным/зеленым становилась не только надпись слева, а и плавающий плейсхолдер
                if(field.inputLink.value.length < 3) {
                    toggleFieldStatus(field.formGroupLink, 'error');
                    userNotifications.notify('Слишком короткий адрес электронной почты!', NOTIFY_WARNING);
                    break;
                }

                let pattern = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
                if(!pattern.test(field.inputLink.value)) {
                    toggleFieldStatus(field.formGroupLink, 'error');
                    userNotifications.notify('Некорректный адрес электронной почты!', NOTIFY_WARNING);
                    break;
                }

                toggleFieldStatus(field.formGroupLink, 'success');
                performNewValueToServer('change-email', field, field.inputLink.value);
                break;
            }

            case 'user_password': {

                let field_additional = getFieldById(additionalFieldId);

                if(field.inputLink.value !== field_additional.inputLink.value) { //проверка совпадения значение обоих полей
                    toggleFieldStatus(field.formGroupLink, 'error');
                    toggleFieldStatus(field_additional.formGroupLink, 'error');
                    break;
                }

                if(field.inputLink.value.length < 4) { //todo мин. и макс. размер нового пароля подтягивать из настроек сервера
                    toggleFieldStatus(field.formGroupLink, 'error');
                    toggleFieldStatus(field_additional.formGroupLink, 'error');
                    userNotifications.notify('Слишком короткий пароль! Минимальная длина: 4 символа.', NOTIFY_WARNING);
                    break;
                }

                if(field.inputLink.value.length > 100) {
                    toggleFieldStatus(field.formGroupLink, 'error');
                    toggleFieldStatus(field_additional.formGroupLink, 'error');
                    userNotifications.notify('Слишком длинный пароль! Максимальная длина: 100 символов.', NOTIFY_WARNING);
                    break;
                }

                toggleFieldStatus(field.formGroupLink, 'success');
                toggleFieldStatus(field_additional.formGroupLink, 'success');
                performNewValueToServer('change-password', field, field.inputLink.value);

                break;
            }

            default: userNotifications.notify('Неизвестное скрипту поле ввода', NOTIFY_WARNING);
        }
    }

    return {
        updateFieldById: updateFieldById
    };
})();