/**
 * Created by Abadonna on 03.02.2016.
 */
$(document).ready(function () {

    //var target = 'local';
    var target = 'server';

    /**
     *
     * @type {*|jQuery|HTMLElement}
     */
    var inputAdd = $('input#addNote');
    /**
     *
     * @type {*|jQuery|HTMLElement}
     */
    var formEdit = $('form#edit');
    /**
     *
     * @type {*|jQuery|HTMLElement}
     */
    var inputTitle = $('input#title');
    /**
     *
     * @type {*|jQuery|HTMLElement}
     */
    var inputText = $('textarea#text');
    /**
     *
     * @type {*|jQuery|HTMLElement}
     */
    var inputSubmit = $('input#saveNote');
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

    var ajaxOptions = {
        type: "POST",
        url: "/service.php",
        data: {
            action: "getAllRecords",
        }
    };

    if (!('notes' in localStorage)) {
        localStorage['notes'] = '';
    }

    /**
     *
     * @param e
     * @returns {boolean}
     */
    var saveChanges = function (e) {
        var noteBlock = e.data.noteBlock;

        e.data.note.title = noteBlock.children('h2:first').text();
        e.data.note.text = noteBlock.children('div.text').text();
        e.data.note.timeEdit = new Date();

        if ('local' == target) {
            var notes = JSON.parse(localStorage['notes']);

            for (var i = 0; i < notes.length; i++) {
                if (notes[i].id == e.data.note.id) {
                    notes[i] = e.data.note;
                    localStorage['notes'] = JSON.stringify(notes);
                    return true;
                }
            }
        } else {
            var options = JSON.parse(JSON.stringify(ajaxOptions));
            //options.data = note;
            options.data.action = 'setRecord';
            options.data.id = e.data.note.id;
            options.data.title = e.data.note.title;
            options.data.text = e.data.note.text;
            options.data.timeAdd = e.data.note.timeAdd.toISOString();
            options.data.timeEdit = e.data.note.timeEdit.toISOString();
            $.ajax(options).done(function(data) {
                if (data.code == '200') {
                    var tmp = JSON.parse(data.data);

                    e.data.note.id = tmp.id;
                    e.data.note.title = tmp.title;
                    e.data.note.text = tmp.text;
                    e.data.note.timeAdd = new Date(e.data.note.timeAdd);
                    e.data.note.timeEdit = new Date(e.data.note.timeEdit);
                    e.data.noteBlock.attr('id', 'note' + e.data.note.id);
                }
            });
        }
        return false;
    };

    /**
     *
     * @param note
     * @param direction
     */
    var drawNote = function (note, direction) {
        var noteBlock = noteBlockSample.clone();
        var timeAdd = new Date(note.timeAdd);
        var timeEdit = new Date(note.timeEdit);

        noteBlock.attr('id', 'node' + note.id);
        noteBlock.children('h2').text(note.title).prop('contentEditable', true).on('input', {
            'note': note,
            'noteBlock': noteBlock
        }, saveChanges);
        noteBlock.children('div.timestamp').text(timeAdd.toLocaleDateString('ru', timeOptions));
        noteBlock.children('div.text').text(note.text).prop('contentEditable', true).on('input', {
            'note': note,
            'noteBlock': noteBlock
        }, saveChanges);
        noteBlock.data('note', note);
        noteBlock.removeClass('sample');
        if (direction == 0) {
            listBlock.prepend(noteBlock);
        } else {
            listBlock.append(noteBlock);
        }
    };

    /**
     *
     * @param data
     * @returns {boolean}
     */
    var showNotes = function (data) {
        var notes = data;
        for (var i = 0; i < notes.length; i++) {
            drawNote(notes[i], 1);
        }
        return false;
    };

    inputAdd.on('click', function () {
        var notes = [];
        note = {
            'id': '',
            'title': 'Title',
            'text': 'text',
            'timeAdd': new Date(),
            'timeEdit': new Date()
        }

        if ('local' == target) {
            if (localStorage['notes']) {
                notes = JSON.parse(localStorage['notes']);
            }
            note.id = notes.length;
            notes.unshift(note);
            localStorage['notes'] = JSON.stringify(notes);
        }
        drawNote(note, 0);
    });

    $('#list').on('click', '.note .delete', function (e) {
        var node = $(e.target.parentNode);
        var notes = JSON.parse(localStorage['notes']);

        if ('local' == target) {
            for (var i = 0; i < notes.length; i++) {
                if (notes[i].id == node.data('note').id) {
                    notes.splice(i, 1);
                    localStorage['notes'] = JSON.stringify(notes);
                    node.remove();
                    return true;
                }
            }
        } else {
            var options = JSON.parse(JSON.stringify(ajaxOptions));
            options.data.action = 'deleteRecord';
            options.data.id = node.data('note').id;
            $.ajax(options).done(function(data) {
                if (data.data) {
                    node.remove();
                    return true;
                }
            });
        }

        return false;
    });


    if ('local' == target) {
        showNotes(JSON.parse(localStorage['notes']));
    } else {
        $.ajax(ajaxOptions).done(function (data) {
            var notes = [];
            for (var i = 0; i < data.data.length; i++) {
                notes[i] = JSON.parse(data.data[i]);
                notes[i].timeAdd = new Date(notes[i].timeAdd);
                notes[i].timeEdit = new Date(notes[i].timeEdit);
            }
            showNotes(notes);
        }).fail(function() {
            alert('error');
        });
    }
});