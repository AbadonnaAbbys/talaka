/**
 * Created by Abadonna on 03.02.2016.
 */
$(document).ready(function () {
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

    if (!('notes' in localStorage)) {
        localStorage['notes'] = '';
    }

    /**
     *
     * @param e
     * @returns {boolean}
     */
    var saveChanges = function (e) {
        var note = e.data.note;
        var noteBlock = e.data.noteBlock;
        var notes = JSON.parse(localStorage['notes']);

        for (var i = 0; i < notes.length; i++) {
            if (notes[i].id == note.id) {
                note.title = noteBlock.children('h2:first').text();
                note.text = noteBlock.children('div.text').text();
                note.timeEdit = new Date();
                notes[i] = note;
                localStorage['notes'] = JSON.stringify(notes);
                return true;
            }
        }
        return false;
    };

    /**
     *
     * @param note
     * @param position
     * @param direction
     */
    var drawNote = function (note, position, direction) {
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

    var showNotes = function () {
        if (localStorage['notes']) {
            var notes = JSON.parse(localStorage['notes']);
            for (var i = 0; i < notes.length; i++) {
                drawNote(notes[i], i, 1);
            }
        }
        return false;
    };

    inputAdd.on('click', function () {
        var notes = [];
        var note = {
            'id': '',
            'title': 'Title',
            'text': 'text',
            'timeAdd': new Date(),
            'timeEdit': new Date()
        }

        if (localStorage['notes']) {
            notes = JSON.parse(localStorage['notes']);
        }
        note.id = notes.length;
        notes.unshift(note);
        localStorage['notes'] = JSON.stringify(notes);
        drawNote(note, notes.length, 0);
    });

    $('#list').on('click', '.note .delete', function(e) {
        var node = $(e.target.parentNode);
        var notes = JSON.parse(localStorage['notes']);

        for (var i = 0; i < notes.length; i++) {
            //console.log(notes[i].id, node.data['id'], notes[i].id == node.data['id']);
            console.log(node);
            if (notes[i].id == node.data('note').id) {
                notes.splice(i, 1);
                localStorage['notes'] = JSON.stringify(notes);
                node.remove();
                return true;
            }
        }
        return false;
    });

    showNotes();

});