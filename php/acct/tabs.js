/**
 * Turns an span inside <li> into a tab.  There are some
 * requirements to make this work:
 *
 *   - background has to be white
 *   - span should be inside <ul> with class "nav"
 *     and <li>
 *   - next node in HTML should be <div> containing
 *     tab content.
 */
function tabify(id) {
  var tabContents = $(id);
  if(tabContents.tabified)
    return;

  addElementClass(tabContents, 'currentTab');
  var parent = tabContents.parentNode;
  var tab = SPAN(null,
       IMG({src: "images/tableft.png"}), 
       tabContents.cloneNode(true),
       IMG({src: "images/tabright.png"}) 
      );
  parent.replaceChild(tab, tabContents);
  $(id).tabified = true; 
}
