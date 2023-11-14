function hideNotif() {
  document.getElementById('notif').innerHTML="";
};

window.setTimeout(hideNotif, 3000);

// NAV
const sidenav = document.getElementById("mySidenav");
const openBtn = document.getElementById("openBtn");
const closeBtn = document.getElementById("closeBtn");

openBtn.onclick = openNav;
closeBtn.onclick = closeNav;

function openNav() {
  sidenav.classList.add("active");
};

function closeNav() {
  sidenav.classList.remove("active");
};

// CONNEXION
document.getElementById('connexion-link').addEventListener('click', function() {
  document.getElementById('connexion-container').classList.remove('hidden');
});
