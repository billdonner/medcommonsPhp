/**
 * Copyright 2006 MedCommons Inc.   All Rights Reserved.
 */

/********************************************************************************************
 * 
 *  MedCommons AutoComplete FormFill Support
 * 
 *  Usage:
 *    1.  Include the autocomplete stylesheet and javascript in your page
 *         eg:
 *           <link href="autoComplete.css" rel="stylesheet" type="text/css"/>
 *           <script type="text/javascript" src="autoComplete.js"></script>
 *
 *    2.  define a div with id "acdiv" on your page.
 *        eg:
 *          <div id="acdiv" ></div>
 *
 *    3.  call the 'init_autocomplete()' function for each field you would like
 *        to auto-complete itself, passing an array of strings that are values.
 *
 *        eg:
 *           init_autocomplete(document.getElementById('somefield'),['Foo','Bar','Dog','Cat']);
 *
 *  Advanced: you can control the behavior by setting autocompleteBehavior - 
 *            see below.
 */

/**
 * Behaviour where all options are shown to the user
 */
var auto_complete_show_options = 
  { 
    select_value: auto_complete_highlight_line,
    show_all: false,
    fill_value: auto_complete_fill_value,
    offsetX: 50,
    offsetY: 14,
    auto_show: true
  };
   
/**
 * Behaviour where only one option is shown to the user
 */
var auto_complete_show_match_only = 
  { 
    select_value: write_inner_html,
    show_all: false,
    fill_value: auto_complete_fill_value,
    offsetX: 50,
    offsetY: 14,
    auto_show: true
  };

/**
 * Default message to show
 */
var auto_complete_default_message = "Press Enter or Click to select entry";

/********************************************************
 *   
 *   set the autocompleteBehavior to control behavior
 *
 ********************************************************/     
var autocompleteBehavior = auto_complete_show_options;

autocompleteBehavior.message = auto_complete_default_message;

var auto_complete_default_behavior = new Object();
for(i in autocompleteBehavior) {
  auto_complete_default_behavior[i] = autocompleteBehavior[i];
}

/********************************************************/
/*  Auto-completion Functions                           */
/********************************************************/     
    var currentCompletion=-1; 
    var autocompleteField = null;

    /**
     * Set up auto complete on a given field
     */
    function init_autocomplete(field, completions) {
      field.values = completions;
      field.onkeyup=autocomplete;
      field.onkeydown=auto_complete_key_down;
      field.setAttribute("autocomplete","off");
      field.autocomplete = autocomplete;
    }

    function auto_complete_key_down(evt) {
      if(!evt)
        evt = window.event;

      autocomplete_taboff(evt);
      if(this.originalValue == null) {
        this.originalValue = this.value;
      }

      if(evt.keyCode == 13) { // enter key
        auto_complete_cancel_evt(evt);
      }
    }
    
    function autocomplete(evt) {
      if(!evt)
        evt = window.event;

      var keyCode = evt ? evt.keyCode : 0;

      field = this;

      var acdiv = document.getElementById('acdiv');
      if(field.autocompleteBehavior && !field.autocompleteBehavior.auto_show) {
        if(keyCode > 0) {// keyCode > 0 means we were invoked by keystroke
          if(acdiv.style.display != 'block') {
            return;
          }
        }
      }

      if(field.autocompleteBehavior) {
        autocompleteBehavior = field.autocompleteBehavior;
      }

      var matchText = field.value;
      //if(autocompleteField == null) {
        autocompleteField = field;
      //}

      if(matchText == "") {
        if(!autocompleteBehavior.show_all) {
          acdiv.style.display='none';
          return;
        }
      }

      if(keyCode == 16) { // shift 
      }
      else
      if(keyCode == 27) {  // escape key 
        auto_complete_reset_field(field);
      }
      else
      if(keyCode == 9) { // tab key 
      }
      else
      if(keyCode == 13) { // enter key
        autocompleteBehavior.fill_value(currentCompletion);
        auto_complete_cancel_evt(evt);
      }
      else
      if(keyCode == 40) { // down arrow 
        currentCompletion++;
        if(currentCompletion>=field.values.length) {
          currentCompletion=0;
        }
        autocompleteBehavior.select_value(currentCompletion);
        return;
      }
      else
      if (keyCode == 38) { // up arrow 
        currentCompletion--;
        if(currentCompletion<0) {
          currentCompletion=field.values.length-1;
        }
        autocompleteBehavior.select_value(currentCompletion);
        return;
      }
      else {
        log("displaying ac 1");
        var m = new RegExp("^" + matchText,"i");
        var found = false;
        for(i=0; i<field.values.length;++i) {
          if(m.test(field.values[i])) {
            found = true;
            currentCompletion=i;
            autocompleteBehavior.select_value(i);
            break;
          }
        }

        log("displaying ac found="+found);
        if(!autocompleteBehavior.show_all && !found) {
          acdiv.innerHTML=matchText;
          currentCompletion=-1;
        }
        else {
          if(!found)
            autocompleteBehavior.select_value(-1);

          var offsetX = findPosX(field);
          var offsetY = findPosY(field);
          acdiv.style.display='block';
          acdiv.style.left=(offsetX + autocompleteBehavior.offsetX)+'px';
          acdiv.style.top=(offsetY + autocompleteBehavior.offsetY)+'px';
        }
      }
    }

    function auto_complete_fill_value(i) {
      var field = autocompleteField;
      if(field == null)
        return;
      field.value = field.values[i];
      field.originalValue = field.value;
      document.getElementById("acdiv").style.display='none';
    }

    function auto_complete_highlight_line(i) {
      var v = autocompleteField.values;
      var html = '';
      for(j=0;j<v.length;++j) {
        if(i==j) {
          html+='<div class="achighlight"><a href="#" onclick="autocompleteBehavior.fill_value('+j+'); return false;" class="achighlight">'+v[j]+'</a></div>';
        }
        else
          html += '<div><a href="#" onclick="javascript:autocompleteBehavior.fill_value('+j+'); return false;">'+v[j] +'</a></div>';
      }
      html+='<div id="acinstr">'+autocompleteBehavior.message+'</div>';
      var acdiv = document.getElementById('acdiv');
      acdiv.innerHTML=html;
    }

    function write_inner_html(i) {
      var acdiv = document.getElementById('acdiv');
      acdiv.innerHTML=autocompleteField.values[i];
    }

/**
 *   selects a value inline - ie. populates the value  
 *   but selects only the portion different to the    
 *   what the user typed                               
 */    
    function auto_complete_inline_select(value) {
      // BROKEN, DOES NOT WORK
      var f = autocompleteField;
      if(!f)
        return;
      var oldValue = f.value;
      f.value = value;
      var r = f.createTextRange();
      r.moveStart('character',oldValue.length);
      r.select();
    }

/********************************************************/
/*   set a field back to its original contents          */
/********************************************************/    
    function auto_complete_reset_field(field) {
      var acdiv = document.getElementById('acdiv');
      if(field) {
        if(field.originalValue) {
          field.value = field.originalValue;
        }
        field.originalValue = null;
        autocompleteField = null;
        field.select();
      }
      acdiv.style.display='none';
    }

/********************************************************/
/* Checks if tab was pressed and if so selects the      */
/* current value                                        */
/********************************************************/    
    function autocomplete_taboff(evt) {
      if(!evt)
        evt = window.event;

      if(evt.keyCode==9) {
        log("currentCompletion = " + currentCompletion);
        if(autocompleteField && (currentCompletion >= 0)) {
          autocompleteField.value = autocompleteField.values[currentCompletion];
          var acdiv = document.getElementById('acdiv');
          acdiv.style.display = 'none';
          currentCompletion = -1;
          autocompleteField = null;
        }
      }
    }


/********************************************************/
/* Prevent default handling of an event
/********************************************************/
function auto_complete_cancel_evt(evtToCancel) {
  if (evtToCancel.stopPropagation)
    evtToCancel.stopPropagation(); // DOM Level 2
  else
     evtToCancel.cancelBubble = true; // IE
     
  if (evtToCancel.preventDefault)
    evtToCancel.preventDefault(); // DOM Level 2
  else
     evtToCancel.returnValue = false; // IE
}
    
function auto_complete_reset_behavior() {
  autocompleteBehavior = new Object();
  for(i in auto_complete_default_behavior) {
    autocompleteBehavior[i] = auto_complete_default_behavior[i];
  }
}
