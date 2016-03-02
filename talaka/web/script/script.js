/**
 * Created by Abadonna on 03.02.2016.
 */
$(document).ready(function () {

    /**
     * Хранилище по умолчанию
     * @type {string}
     */
    var target = 'local';
    //var target = 'server';

    /**
     *
     * @type {*|jQuery|HTMLElement}
     */
    var inputAdd = $('input#addNote');
    /**
     *
     * @type {*|jQuery|HTMLElement}
     */
    var listBlock = $('#list:first');
    /**
     *
     * @type {*|jQuery|HTMLElement}
     */
    var noteBlockSample = $('.note, .sample');

    /**
     *
     * @type {{year: string, month: string, day: string, timezone: string, hour: string, minute: string, second: string}}
     */
    var timeOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        timezone: 'UTC',
        hour: 'numeric',
        minute: 'numeric',
        second: 'numeric'
    };

    /**
     * Общие настройки соединения с сервером
     * @type {{type: string, url: string, data: {action: string}}}
     */
    var ajaxOptions = {
        type: "POST",
        url: "/service.php",
        data: {
            action: "getAllRecords"
        }
    };

    /**
     * Прототип класса Note
     * Содержит поля заметки и логику работы с внешним и локальным хранилищами
     * @param ajaxOptions
     * @constructor
     */
    function Note(ajaxOptions) {
        /**
         * @type {number}
         */
        this.id = 0;
        /**
         * @type {string}
         */
        this.title = 'title';
        /**
         * @type {string}
         */
        this.text = 'text';
        /**
         * @type {Date}
         */
        this.timeAdd = new Date();
        /**
         * @type {Date}
         */
        this.timeEdit = new Date();
        /**
         * @type {*|jQuery|HTMLElement}
         */
        this.block = null;
        /**
         * Статус запланированного соединения с сервером.
         * Используется для выполнения следующего запроса к серверу по данной записи после окончания обработки предыдущего запроса.
         * Запросы ставятся в очередь для того, чтобы одна записка не сохранялась на сервере в несколько записей.
         * Может принимать три значения: "done", "save" и "delete".
         * В случае, если значение статуса "delete", значение не может быть изменено.
         * @type {string}
         */
        this.status = 'done';
        /**
         * @type {Object}
         */
        this.options = JSON.parse(JSON.stringify(ajaxOptions));
        /**
         * @type {string}
         */
        this.options.data.timeAdd = this.timeAdd.toISOString();
        /**
         * Устанавливает ID для текущей заметки и обновляет атрибут id связанного с заметкой блока
         * @param id
         */
        this.setId = function (id) {
            this.id = id;
            if (this.block == null) {
                console.log('Block for note #' + id + ' not defined');
            } else {
                this.block.attr('id', 'note' + this.id);
            }
        };
        /**
         *
         */
        this.setStatusDone = function () {
            this.status = 'done';
        };
        /**
         *
         */
        this.setStatusSave = function () {
            if (this.status != 'delete') {
                this.status = 'save';
            }
        };
        /**
         *
         */
        this.setStatusDelete = function () {
            this.status = 'delete';
        };
        /**
         * Устанавливает ID для текущей заметки равным значению возвращенному с сервера
         * @param data
         */
        this.updateFromServer = function (data) {
            if (data.code == '200') {
                if (!this.id) {
                    var tmp = JSON.parse(data.data);
                    this.setId(tmp.id);
                }
            }
        };
        /**
         *
         */
        this.saveToServer = function () {
            this.options.data.action = 'setRecord';
            this.options.data.id = this.id;
            this.options.data.title = this.title;
            this.options.data.text = this.text;
            this.options.data.timeEdit = this.timeEdit.toISOString();
            this.request = $.ajax(this.options);
            this.request.done($.proxy(this.updateFromServer, this));
            this.setStatusDone();
        };
        /**
         *
         */
        this.saveToLocal = function () {
            var notes;
            if (localStorage['notes']) {
                notes = JSON.parse(localStorage['notes']);
            }
            if (!this.id) {
                var i = 0;
                if (localStorage['notesLastId']) {
                    i = parseInt(localStorage['notesLastId']);
                }
                this.setId(i + 1);
            }
            localStorage.setItem('notesLastId', this.id);
            if (notes) {
                var isNew = true;
                for (var j = 0; j < notes.length; j++) {
                    if (notes[j].id == this.id) {
                        notes[j] = this.getDataObject();
                        isNew = false;
                        break;
                    }
                }
                if (isNew) {
                    notes.unshift(this.getDataObject());
                }
            } else {
                notes = [this.getDataObject()];
            }
            localStorage['notes'] = JSON.stringify(notes);
        };
        /**
         * Сохраняет текущее состояние заметки
         */
        this.save = function () {
            this.title = this.block.children('h2').text();
            this.text = this.block.children('div.text').text();
            this.timeEdit = new Date();
            if ('local' == target) {
                this.saveToLocal();
            } else {
                this.setStatusSave();
                //this.saveToServer();
            }
        };
        /**
         * Возвращает значения полей заметки для сериализации
         * @returns {{id: *, title: *, text: *, timeAdd: *, timeEdit: *}}
         */
        this.getDataObject = function () {
            return {
                id: this.id,
                title: this.title,
                text: this.text,
                timeAdd: this.timeAdd,
                timeEdit: this.timeEdit
            };
        };
        /**
         * Присваивает полям заметки значения, переданые в объекте при совпадении имен полей
         * @param object
         */
        this.setData = function (object) {
            if (!this.id && object.id) {
                this.id = object.id;
            }
            if (object.title) {
                this.title = object.title;
            }
            if (object.text) {
                this.text = object.text;
            }
            if (object.timeAdd) {
                if (object.timeAdd instanceof Date) {
                    this.timeAdd = object.timeAdd;
                } else {
                    this.timeAdd = new Date(object.timeAdd);
                }
            }
            if (object.timeEdit) {
                if (object.timeAdd instanceof Date) {
                    this.timeEdit = object.timeEdit;
                } else {
                    this.timeEdit = new Date(object.timeAdd);
                }
            }
        };
        this.deleteFromServer = function () {
            this.options.data.action = 'deleteRecord';
            this.options.data.id = this.id;

            this.request = $.ajax(this.options).done($.proxy(this.remove, this));
            this.setStatusDone();
        };
        this.deleteFromLocal = function () {
            var notes = JSON.parse(localStorage['notes']);
            for (var i = 0; i < notes.length; i++) {
                if (notes[i].id == this.id) {
                    notes.splice(i, 1);
                    localStorage['notes'] = JSON.stringify(notes);
                    this.block.remove();
                    return true;
                }
            }
            return false;
        };
        /**
         * Удаляет заметку из хранилища
         */
        this.delete = function () {
            if ('local' == target) {
                this.deleteFromLocal();
            } else {
                this.setStatusDelete();
                //this.deleteFromServer();
            }
        };
        /**
         * Обработчик очереди запросов к серверу.
         * Выполняет следующий запрос в случае, если выполнен предыдущий запрос
         */
        this.do = function () {
            if (this.request && this.request.readyState == 4) {
                if (this.status == 'save') {
                    this.saveToServer();
                } else if (this.status == 'delete') {
                    this.deleteFromServer();
                }
            } else {
                if (this.status == 'save') {
                    this.saveToServer();
                } else if (this.status == 'delete') {
                    this.deleteFromServer();
                }
            }
        };
        /**
         * Удаляет блок, привязанный к заметке со страницы если data.data = true
         * @param data
         * @returns {boolean}
         */
        this.remove = function (data) {
            if (data.data) {
                this.block.remove();
                return true;
            }
            return false;
        };
        /**
         * Создает для текущей заметки блок на странице из скрытого блока-шаблона
         * Если direction == 0 - вставляет блок в начало списка
         * Если direction == 1 - вставляет блок в конец списка
         * @param direction
         */
        this.draw = function (direction) {
            this.block = noteBlockSample.clone();
            this.block.attr('id', 'node' + this.id);
            this.block.children('h2').text(this.title).prop('contentEditable', true).on('input', $.proxy(this.save, this));
            this.block.children('div.timestamp').text(this.timeAdd.toLocaleDateString('ru', timeOptions));
            this.block.children('div.text').text(this.text).prop('contentEditable', true).on('input', $.proxy(this.save, this));
            this.block.children('.delete').on('click', $.proxy(this.delete, this));
            this.block.data('note', this);
            if (direction == 0) {
                listBlock.prepend(this.block);
            } else {
                listBlock.append(this.block);
            }
            this.block.removeClass('sample');
        };

        /**
         * Запускает обработчик очереди запросов 1 раз в секунду. Только для удаленного хранилища
         */
        if ('server' == target) {
            setInterval($.proxy(this.do, this), 1000);
        }
    }

    if (!('notes' in localStorage)) {
        localStorage['notes'] = '';
    }

    /**
     * Список заметок
     * @type {Array}
     */
    var notes = [];

    /**
     * Выводит на страницу набор заметок
     * @param data
     */
    var showNotes = function (data) {
        if (data) {
            for (var i = 0; i < data.length; i++) {
                notes[i] = new Note(ajaxOptions);
                notes[i].setData(data[i]);
                notes[i].draw(1);
            }
        }
    };

    /**
     * Добавляет на страницу новую заметку по клику на кнопке
     */
    inputAdd.on('click', function () {
        var note = new Note(ajaxOptions);
        note.draw(0);
        note.save();
    });

    /**
     * Переключает место хранения заметок. При этом страница перезагружается.
     */
    $("input:radio[name=target]").on('change', function () {
        target = $(this).val();
        localStorage['target'] = target;
        location.reload();
    });

    /**
     * Устанавливаем сохраненное расположение хранилища. Если сохраненного значения нет, устанавливаем расположение по умолчанию.
     */
    if (localStorage['target']) {
        target = localStorage['target'];
    }
    localStorage['target'] = target;
    $("input:radio[name=target][value=" + target + "]").prop('checked', true);

    /**
     * Загружаем и выводим заметки, хранящиеся в установленном хранилище
     */
    if ('local' == target) {
        var data;
        if (localStorage.getItem('notes')) {
            data = JSON.parse(localStorage.getItem('notes'));
        }
        showNotes(data);
    } else {
        $.ajax(ajaxOptions).done(function (data) {
            var notes = [];
            for (var i = 0; i < data.data.length; i++) {
                notes[i] = JSON.parse(data.data[i]);
                notes[i].timeAdd = new Date(notes[i].timeAdd);
                notes[i].timeEdit = new Date(notes[i].timeEdit);
            }
            showNotes(notes);
        }).fail(function () {
            alert('error');
        });
    }
});