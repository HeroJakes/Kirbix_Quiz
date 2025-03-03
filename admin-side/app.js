document.addEventListener('DOMContentLoaded', function () {
  const toggleButton = document.getElementById('toggle-btn');
  const sidebar = document.getElementById('sidebar');

  toggleButton.addEventListener('click', toggleSidebar);

  function toggleSidebar() {
    sidebar.classList.toggle('close');
    toggleButton.classList.toggle('rotate');
    closeAllSubMenus();
  }
});


