// =====================================================
// app.js - kleine stukjes JavaScript voor EasyEnglish
// De website werkt ook zonder JavaScript; dit maakt het alleen prettiger.
// =====================================================

document.addEventListener('DOMContentLoaded', function () {

  // ---------- Wachtwoord tonen / verbergen (oogje) ----------
  document.querySelectorAll('.toggle-password').forEach(function (button) {
    button.addEventListener('click', function () {
      const input = button.parentElement.querySelector('input');
      input.type = input.type === 'password' ? 'text' : 'password';
    });
  });

  // ---------- Eerst vragen voordat je een lijst verwijdert ----------
  document.querySelectorAll('.confirm-delete').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!confirm(form.dataset.confirm)) {
        event.preventDefault();
      }
    });
  });

  // ---------- Filters in de bibliotheek direct toepassen ----------
  document.querySelectorAll('.auto-submit').forEach(function (select) {
    select.addEventListener('change', function () {
      select.form.submit();
    });
  });

  // ---------- Toets bouwer: rijen toevoegen en verwijderen ----------
  const rowsContainer = document.getElementById('word-rows');
  if (rowsContainer) {
    const addButton = document.getElementById('add-row');
    const wordCount = document.getElementById('word-count');

    // De gestippelde lege rij onderaan
    function getNewRow() {
      return rowsContainer.querySelector('.new-row');
    }

    // Telt de rijen waar iets in is ingevuld
    function updateCount() {
      let count = 0;
      rowsContainer.querySelectorAll('.word-row:not(.word-row-head)').forEach(function (row) {
        const inputs = row.querySelectorAll('input');
        if (inputs[0].value.trim() !== '' || inputs[1].value.trim() !== '') {
          count++;
        }
      });
      wordCount.textContent = count;
    }

    // Maakt van de gestippelde rij een gewone rij en zet een nieuwe lege rij eronder
    function promoteNewRow() {
      const row = getNewRow();
      row.classList.remove('new-row');
      row.querySelector('.remove-row').disabled = false;

      const freshRow = row.cloneNode(true);
      freshRow.classList.add('new-row');
      freshRow.querySelectorAll('input').forEach(function (input) { input.value = ''; });
      freshRow.querySelector('.remove-row').disabled = true;
      rowsContainer.appendChild(freshRow);
    }

    // Typen in de gestippelde rij? Dan komt er automatisch een nieuwe bij.
    rowsContainer.addEventListener('input', function (event) {
      if (event.target.closest('.new-row')) {
        promoteNewRow();
      }
      updateCount();
    });

    // Prullenbak-knop: rij weghalen
    rowsContainer.addEventListener('click', function (event) {
      const removeButton = event.target.closest('.remove-row');
      if (removeButton && !removeButton.disabled) {
        removeButton.closest('.word-row').remove();
        updateCount();
      }
    });

    // "Rij Toevoegen": zet de cursor in de lege rij, of maak een nieuwe
    addButton.addEventListener('click', function () {
      const row = getNewRow();
      const firstInput = row.querySelector('input');
      if (firstInput.value.trim() === '') {
        firstInput.focus();
      } else {
        promoteNewRow();
        getNewRow().querySelector('input').focus();
      }
    });

    updateCount();
  }
});
