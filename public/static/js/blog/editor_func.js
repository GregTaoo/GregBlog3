function change_cursor_inline(delta) {
    let cs = editor.getCursor();
    let ch = cs.ch + delta, ln = cs.line;
    setTimeout(function () {
        editor.setCursor(ln, ch);
        editor.focus();
    }, 100);
}
function editor_surround(str) {
    change_cursor_inline(str.length);
    editor.replaceSelection(str + editor.getSelection() + str);
}
function editor_tag(str) {
    change_cursor_inline(str.length + 2);
    editor.replaceSelection("[" + str + "]" + editor.getSelection() + "[/" + str + "]");
}
function editor_add(str) {
    change_cursor_inline(str.length);
    editor.replaceSelection(str);
}
function editor_title(x) {
    editor.replaceSelection("#".repeat(x) + " ");
    editor.focus();
}
function editor_bold() {
    editor_surround("**");
}
function editor_incline() {
    editor_surround("*");
}
function editor_delete_line() {
    editor_surround("~~");
}
function editor_katex(x) {
    editor_surround("$".repeat(x));
}
function editor_link() {
    let slct = editor.getSelection();
    if (slct.length > 0) {
        editor.replaceSelection("[](" + slct + ")");
    } else {
        add_lnk.modal('show');
    }
}
function editor_link_insert(link, txt) {
    editor.replaceSelection("[" + txt + "](" + link + ")")
}
function editor_img() {
    let slct = editor.getSelection();
    if (slct.length > 0) {
        editor.replaceSelection("![](" + slct + ")");
    } else {
        add_img.modal('show');
    }
}
function editor_img_insert(link, txt) {
    editor.replaceSelection("![" + txt + "](" + link + ")")
}
function editor_quote() {
    editor.replaceSelection("> ");
    editor.focus();
}
function editor_color(color, fix) {
    if (fix) {
        editor.replaceSelection("[style color:" + color + "]" + editor.getSelection() + "[/style]");
        change_cursor_inline(-8);
    }
    else editor.replaceSelection(color);
}
function editor_code(lang) {
    editor.replaceSelection("```" + lang + '\n' + editor.getSelection() + "\n```");
}