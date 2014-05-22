var skipClientValidation = false;

function qf_errorHandler(element, _qfMsg) {
  div = element.parentNode;

  if ((div == undefined) || (element.name == undefined)) {
    //no checking can be done for undefined elements so let server handle it.
    return true;
  }


  if (_qfMsg != '') {
    var errorSpan = document.getElementById('id_error_'+element.name);
    if (!errorSpan) {
      errorSpan = document.createElement("span");
      errorSpan.id = 'id_error_'+element.name;
      errorSpan.className = "error";
      element.parentNode.insertBefore(errorSpan, element.parentNode.firstChild);
      document.getElementById(errorSpan.id).setAttribute('TabIndex', '0');
      document.getElementById(errorSpan.id).focus();
    }

    while (errorSpan.firstChild) {
      errorSpan.removeChild(errorSpan.firstChild);
    }

    errorSpan.appendChild(document.createTextNode(_qfMsg.substring(3)));

    if (div.className.substr(div.className.length - 6, 6) != " error"
      && div.className != "error") {
        div.className += " error";
        linebreak = document.createElement("br");
        linebreak.className = "error";
        linebreak.id = 'id_error_break_'+element.name;
        errorSpan.parentNode.insertBefore(linebreak, errorSpan.nextSibling);
    }

    return false;
  } else {
    var errorSpan = document.getElementById('id_error_'+element.name);
    if (errorSpan) {
      errorSpan.parentNode.removeChild(errorSpan);
    }
    var linebreak = document.getElementById('id_error_break_'+element.name);
    if (linebreak) {
      linebreak.parentNode.removeChild(linebreak);
    }

    if (div.className.substr(div.className.length - 6, 6) == " error") {
      div.className = div.className.substr(0, div.className.length - 6);
    } else if (div.className == "error") {
      div.className = "";
    }

    return true;
  }
}
function validate_mod_forum_post_form_subject(element) {
  if (undefined == element) {
     //required element was not found, then let form be submitted without client side validation
     return true;
  }
  var value = '';
  var errFlag = new Array();
  var _qfGroups = {};
  var _qfMsg = '';
  var frm = element.parentNode;
  if ((undefined != element.name) && (frm != undefined)) {
      while (frm && frm.nodeName.toUpperCase() != "FORM") {
        frm = frm.parentNode;
      }
      value = frm.elements['subject'].value;
  if (value == '' && !errFlag['subject']) {
    errFlag['subject'] = true;
    _qfMsg = _qfMsg + '\n - Required';
  }

  value = frm.elements['subject'].value;
  if (value != '' && value.length > 255 && !errFlag['subject']) {
    errFlag['subject'] = true;
    _qfMsg = _qfMsg + '\n - Maximum of 255 characters';
  }

      return qf_errorHandler(element, _qfMsg);
  } else {
    //element name should be defined else error msg will not be displayed.
    return true;
  }
}

function validate_mod_forum_post_form_message_5btext_5d(element) {
  if (undefined == element) {
     //required element was not found, then let form be submitted without client side validation
     return true;
  }
  var value = '';
  var errFlag = new Array();
  var _qfGroups = {};
  var _qfMsg = '';
  var frm = element.parentNode;
  if ((undefined != element.name) && (frm != undefined)) {
      while (frm && frm.nodeName.toUpperCase() != "FORM") {
        frm = frm.parentNode;
      }
      value = frm.elements['message[text]'].value;
  if (value == '' && !errFlag['message[text]']) {
    errFlag['message[text]'] = true;
    _qfMsg = _qfMsg + '\n - Required';
  }

      return qf_errorHandler(element, _qfMsg);
  } else {
    //element name should be defined else error msg will not be displayed.
    return true;
  }
}

function validate_mod_forum_post_form(frm) {
  if (skipClientValidation) {
     return true;
  }
  var ret = true;

  var frm = document.getElementById('mformforum')
  var first_focus = false;

  ret = validate_mod_forum_post_form_subject(frm.elements['subject']) && ret;
  if (!ret && !first_focus) {
    first_focus = true;
    document.getElementById('id_error_subject').focus();
  }

  ret = validate_mod_forum_post_form_message_5btext_5d(frm.elements['message[text]']) && ret;
  if (!ret && !first_focus) {
    first_focus = true;
    document.getElementById('id_error_message[text]').focus();
  }
;
  return ret;
}
