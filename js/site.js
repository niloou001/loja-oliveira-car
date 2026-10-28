'use strict';
(() => {
  const storageKey = 'oliveira-car-favoritos';
  let favorites = new Set();
  try {
    const saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
    if (Array.isArray(saved)) favorites = new Set(saved.filter(Number.isInteger));
  } catch (_) { /* Os controles continuam funcionando sem armazenamento persistente. */ }
  let toastTimer;
  const announce = (message) => {
    const toast = document.querySelector('#toast');
    toast.textContent = message; toast.hidden = false;
    clearTimeout(toastTimer); toastTimer = setTimeout(() => { toast.hidden = true; }, 3500);
  };
  const syncButtons = () => {
    document.querySelectorAll('[data-favorite]').forEach(button => {
      const selected = favorites.has(Number(button.dataset.favorite));
      button.setAttribute('aria-pressed', String(selected));
      button.setAttribute('aria-label', `${selected ? 'Remover' : 'Salvar'} ${button.dataset.label} ${selected ? 'dos' : 'nos'} favoritos`);
      button.textContent = button.hasAttribute('data-favorite-text') ? (selected ? '♥ Salvo nos favoritos' : '♡ Salvar nos favoritos') : (selected ? '♥' : '♡');
    });
  };
  const form = document.querySelector('#filters');
  const grid = document.querySelector('#vehicle-grid');
  let onlySaved = new URLSearchParams(location.search).get('favoritos') === '1';
  const normalize = (s) => s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
  const applyFilters = () => {
    if (!grid) return;
    const search = normalize(document.querySelector('#search').value);
    const brand = document.querySelector('#brand').value;
    const maxPrice = Number(document.querySelector('#price').value) || Infinity;
    const sort = document.querySelector('#sort').value;
    const cards = Array.from(grid.querySelectorAll('[data-vehicle]'));
    cards.sort((a, b) => sort === 'price-asc' ? Number(a.dataset.price) - Number(b.dataset.price) : sort === 'price-desc' ? Number(b.dataset.price) - Number(a.dataset.price) : sort === 'name' ? a.dataset.name.localeCompare(b.dataset.name, 'pt-BR') : Number(a.dataset.id) - Number(b.dataset.id));
    let count = 0;
    for (const card of cards) {
      const show = normalize(`${card.dataset.name} ${card.dataset.brand}`).includes(search) && (!brand || card.dataset.brand === brand) && Number(card.dataset.price) <= maxPrice && (!onlySaved || favorites.has(Number(card.dataset.id)));
      card.hidden = !show; count += Number(show); grid.append(card);
    }
    document.querySelector('#results-count').textContent = `${count} ${count === 1 ? 'veículo encontrado' : 'veículos encontrados'}`;
    document.querySelector('#empty-state').hidden = count > 0;
    document.querySelector('#saved-filter').setAttribute('aria-pressed', String(onlySaved));
  };
  document.querySelectorAll('[data-favorite]').forEach(button => button.addEventListener('click', () => {
    const id = Number(button.dataset.favorite);
    favorites.has(id) ? favorites.delete(id) : favorites.add(id);
    let persistent = true;
    try { localStorage.setItem(storageKey, JSON.stringify([...favorites])); } catch (_) { persistent = false; }
    syncButtons(); applyFilters();
    announce(favorites.has(id) ? `Veículo salvo${persistent ? ' nos favoritos.' : ' nesta sessão. O navegador bloqueou o armazenamento.'}` : 'Veículo removido dos favoritos.');
  }));
  syncButtons();
  if (form) {
    form.addEventListener('submit', e => e.preventDefault());
    form.addEventListener('input', applyFilters);
    form.addEventListener('change', applyFilters);
    form.addEventListener('reset', () => { onlySaved = false; setTimeout(applyFilters, 0); });
    document.querySelector('#sort').addEventListener('change', applyFilters);
    document.querySelector('#saved-filter').addEventListener('click', () => { onlySaved = !onlySaved; applyFilters(); });
    document.querySelector('#clear-all').addEventListener('click', () => form.reset());
    applyFilters();
  }
  const simulator = document.querySelector('[data-simulator]');
  if (simulator) {
    const price = Number(simulator.dataset.value), rate = Number(simulator.dataset.interest), free = Number(simulator.dataset.free);
    const downPayment = document.querySelector('#down-payment'), months = document.querySelector('#months');
    const money = value => value.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
    const recalculate = () => {
      const down = Number(downPayment.value), n = Number(months.value);
      const invalid = downPayment.value === '' || !Number.isFinite(down) || down < 0 || down > price || !downPayment.validity.valid;
      downPayment.setAttribute('aria-invalid', String(invalid));
      document.querySelector('#payment-error').hidden = !invalid;
      if (invalid) {
        document.querySelector('#payment-value').textContent = '—';
        document.querySelector('#payment-total').textContent = '—';
        document.querySelector('#payment-label').textContent = 'Revise o valor da entrada';
        document.querySelector('#payment-interest').textContent = '';
        return;
      }
      const principal = Math.round((price - down) * 100) / 100;
      const interest = n <= free ? 0 : principal * (rate / 100) * n;
      document.querySelector('#payment-label').textContent = principal === 0 ? 'Saldo quitado com a entrada' : `${n} ${n === 1 ? 'parcela de' : 'parcelas de'}`;
      document.querySelector('#payment-value').textContent = money((principal + interest) / n);
      document.querySelector('#payment-interest').textContent = n <= free || principal === 0 ? 'Sem juros' : `Juros simples de ${rate.toLocaleString('pt-BR')}% ao mês`;
      document.querySelector('#payment-total').textContent = money(price + interest);
    };
    downPayment.addEventListener('input', recalculate); months.addEventListener('change', recalculate); recalculate();
  }
  document.querySelector('#share-vehicle')?.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(location.href); announce('Link copiado!'); }
    catch (_) { announce('Copie o endereço na barra do navegador para compartilhar.'); }
  });
  document.querySelectorAll('[data-car-image]').forEach(img => {
    const fallback = () => { if (!img.dataset.fallback) { img.dataset.fallback = '1'; img.src = 'img/veiculo-placeholder.svg'; } };
    img.addEventListener('error', fallback);
    if (img.complete && img.naturalWidth === 0) fallback();
  });
})();
