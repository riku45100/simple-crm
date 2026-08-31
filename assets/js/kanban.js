(function () {
  const board = document.getElementById('simple-crm-kanban-board');
  if (!board) return;

  let draggedCard = null;

  board.addEventListener('dragstart', function (e) {
    const card = e.target.closest('.simple-crm-kanban-card');
    if (!card) return;
    draggedCard = card;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', card.dataset.dealId);
  });

  board.addEventListener('dragover', function (e) {
    e.preventDefault();
    const column = e.target.closest('.simple-crm-kanban-column');
    if (!column) return;
    const cardsContainer = column.querySelector('.simple-crm-kanban-cards');
    if (!cardsContainer) return;
    cardsContainer.style.background = '#f0f0f0';
  });

  board.addEventListener('dragleave', function (e) {
    const column = e.target.closest('.simple-crm-kanban-column');
    if (!column) return;
    const cardsContainer = column.querySelector('.simple-crm-kanban-cards');
    if (!cardsContainer) return;
    cardsContainer.style.background = '';
  });

  board.addEventListener('drop', function (e) {
    e.preventDefault();
    const column = e.target.closest('.simple-crm-kanban-column');
    if (!column || !draggedCard) return;

    const newStage = column.dataset.stage;
    const dealId = draggedCard.dataset.dealId;

    const cardsContainer = column.querySelector('.simple-crm-kanban-cards');
    cardsContainer.style.background = '';
    cardsContainer.appendChild(draggedCard);
    draggedCard = null;

    updateStageCounts();

    const formData = new FormData();
    formData.append('action', simpleCrmKanban.action);
    formData.append('nonce', simpleCrmKanban.nonce);
    formData.append('deal_id', dealId);
    formData.append('stage', newStage);

    fetch(simpleCrmKanban.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) {
          alert('Failed to update stage: ' + (data.data?.message || 'Unknown error'));
        }
      })
      .catch(() => {
        alert('Network error while updating stage.');
      });
  });

  function updateStageCounts() {
    const columns = board.querySelectorAll('.simple-crm-kanban-column');
    columns.forEach((col) => {
      const stage = col.dataset.stage;
      const count = col.querySelectorAll('.simple-crm-kanban-card').length;
      const counter = col.querySelector('.simple-crm-stage-count');
      if (counter) counter.textContent = count;
    });
  }
})();
