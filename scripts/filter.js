function filterList() {
  gFilterTimeout = null;
  text = document.getElementById("filterbox").value.toLowerCase();
  var sections = document.getElementsByClassName("filtersection");
  for (var i = 0; i < sections.length; i++) {
    var showSection = false;
    var elements = sections[i].getElementsByClassName("filteritem");
    for (var j = 0; j < elements.length; j++) {
      if (text == "") {
        elements[j].style.display = null;
        showSection = true;
      }
      else if (elements[j].textContent.toLowerCase().indexOf(text) >= 0) {
        elements[j].style.display = null;
        showSection = true;
      }
      else {
        elements[j].style.display = "none";
      }
    }
    sections[i].style.display = showSection ? null : "none";
  }
}

var gFilterTimeout = null;
function filterChange() {
  if (gFilterTimeout)
    window.clearTimeout(gFilterTimeout);

  gFilterTimeout = window.setTimeout(filterList, 500);
}

function clearFilter() {
  document.getElementById("filterbox").value = "";
  filterList();
}
