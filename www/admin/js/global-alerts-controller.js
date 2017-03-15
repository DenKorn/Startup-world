/**
 * Created by korne on 13.03.2017.
 */
'use strict';

let globalAlertsController = (function () {

    /**
     * Проверяет параметры формы и отправляет её, если всё хорошо. Иначе, уведомляет об этом пользователя
     *
     * @param formObject Object
     */
    function validateAndSubmit(formObject) {
        if(!formObject.message.value) {
            alert('Не забудьте ввести сообщение!');
            return false;
        } else if(formObject.message.value.length > 255){
            alert('Превышена максимальная длина сообщения в 255 символов! Извините за неудобства, дорогие админушки, но пока что так...');
            return false;
        } else {
            return formObject.submit();
        }
    }
    
    function init() {
        document.querySelector('#messageTextArea').addEventListener('keypress', function (event) {
            if(event.keyCode==10||(event.ctrlKey && event.keyCode==13))
                validateAndSubmit(this.form);
        });

        document.querySelector('#formSubmitBtn').addEventListener('click', function (event) {
            validateAndSubmit(this.form);
        })
    }
    
    init();
})();