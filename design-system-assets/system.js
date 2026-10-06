/* All demonstrations stay in memory. No network request or persistent storage. */
(() => {
  const tabs = [...document.querySelectorAll('[data-tab]')];
  function selectTab(id, focus = false) {
    tabs.forEach((tab) => {
      const selected = tab.dataset.tab === id;
      tab.setAttribute('aria-selected', String(selected));
      tab.tabIndex = selected ? 0 : -1;
      document.getElementById(tab.getAttribute('aria-controls')).hidden = !selected;
      if (selected && focus) tab.focus();
    });
  }
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => selectTab(tab.dataset.tab));
    tab.addEventListener('keydown', (event) => {
      let next;
      if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
      if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
      if (event.key === 'Home') next = 0;
      if (event.key === 'End') next = tabs.length - 1;
      if (next !== undefined) {
        event.preventDefault();
        selectTab(tabs[next].dataset.tab, true);
      }
    });
  });
  document.querySelectorAll('[data-open-form]').forEach((button) => {
    button.addEventListener('click', () => {
      selectTab(button.dataset.openForm, true);
      document.getElementById('forms').scrollIntoView({ block: 'start' });
    });
  });

  const search = document.getElementById('resource-search');
  const filters = [...document.querySelectorAll('[data-filter]')];
  const resources = [...document.querySelectorAll('[data-resource]')];
  let category = 'all';
  function filterResources() {
    const query = search.value.trim().toLocaleLowerCase();
    let count = 0;
    resources.forEach((row) => {
      const show = (category === 'all' || row.dataset.resource === category) && row.textContent.toLocaleLowerCase().includes(query);
      row.hidden = !show;
      if (show) count += 1;
    });
    document.getElementById('resource-count').textContent = `${count} resource${count === 1 ? '' : 's'}`;
    document.getElementById('resource-empty').hidden = count !== 0;
    document.getElementById('resource-preview').hidden = true;
  }
  search.addEventListener('input', filterResources);
  filters.forEach((button) => button.addEventListener('click', () => {
    category = button.dataset.filter;
    filters.forEach((filter) => filter.setAttribute('aria-pressed', String(filter === button)));
    filterResources();
  }));
  document.getElementById('clear-resources').addEventListener('click', () => {
    search.value = '';
    filters[0].click();
    search.focus();
  });
  const previewCopy = {
    manuals: ['Manuals', 'Sample destination: /general/manuals/. The live component will open the current resource and identify file type, date and size when available.'],
    safety: ['Safety Plan', 'Sample destination: /general/safety-plan/. Keep campus safety information easy to access without requesting contact details.'],
    heerf: ['HEERF Quarterly Reports', 'Sample destination: /general/heerf-quarterly-reports/. Group reports by period, preserving the complete document title.'],
    graduation: ['Graduation Requirements', 'Sample destination: /general/graduation-requirements/. Present requirements as readable content with supporting documents.']
  };
  document.querySelectorAll('[data-preview]').forEach((button) => button.addEventListener('click', () => {
    const preview = document.getElementById('resource-preview');
    const [title, copy] = previewCopy[button.dataset.preview];
    const heading = document.createElement('strong');
    const paragraph = document.createElement('p');
    heading.textContent = title;
    paragraph.textContent = copy;
    preview.replaceChildren(heading, paragraph);
    preview.hidden = false;
  }));

  const visit = document.getElementById('visit-form');
  const localDate = new Date();
  document.getElementById('visit-date').min = `${localDate.getFullYear()}-${String(localDate.getMonth() + 1).padStart(2, '0')}-${String(localDate.getDate()).padStart(2, '0')}`;

  function clearError(control) {
    const errorId = `${control.id}-error`;
    document.getElementById(errorId)?.remove();
    control.removeAttribute('aria-invalid');
    const described = (control.getAttribute('aria-describedby') || '').split(' ').filter((id) => id && id !== errorId);
    if (described.length) control.setAttribute('aria-describedby', described.join(' '));
    else control.removeAttribute('aria-describedby');
  }
  function errorMessage(control) {
    if (control.required && !control.value.trim()) return control.tagName === 'SELECT' ? 'Choose an option to continue.' : 'Please complete this field.';
    if (control.validity.typeMismatch) return 'Enter a complete email, like alex@example.com.';
    if (control.validity.rangeUnderflow) return 'Choose today or a future date.';
    if (!control.validity.valid) return 'Check this value and try again.';
    return '';
  }
  function validate(scope, form) {
    const controls = [...scope.querySelectorAll('input:not([type=radio]), select, textarea')];
    let firstInvalid;
    let invalidCount = 0;
    controls.forEach((control) => {
      clearError(control);
      const message = errorMessage(control);
      if (!message) return;
      invalidCount += 1;
      firstInvalid ||= control;
      const error = document.createElement('small');
      error.id = `${control.id}-error`;
      error.className = 'field-error';
      error.textContent = message;
      control.closest('.field').append(error);
      control.setAttribute('aria-invalid', 'true');
      control.setAttribute('aria-describedby', [control.getAttribute('aria-describedby'), error.id].filter(Boolean).join(' '));
    });
    form.querySelector('.form-status').textContent = invalidCount ? `Please review ${invalidCount} field${invalidCount === 1 ? '' : 's'} below.` : '';
    firstInvalid?.focus();
    return !invalidCount;
  }
  function showVisitStep(step, focus = true) {
    visit.querySelectorAll('[data-visit-step]').forEach((panel) => { panel.hidden = panel.dataset.visitStep !== String(step); });
    [1, 2].forEach((number) => {
      const indicator = document.getElementById(`visit-progress-${number}`);
      indicator.classList.toggle('active', number <= step);
      if (number === step) indicator.setAttribute('aria-current', 'step');
      else indicator.removeAttribute('aria-current');
    });
    visit.querySelector('.form-status').textContent = '';
    if (focus) visit.querySelector(`[data-visit-step="${step}"] input, [data-visit-step="${step}"] select`).focus();
  }
  document.getElementById('visit-next').addEventListener('click', () => {
    if (validate(visit.querySelector('[data-visit-step="1"]'), visit)) showVisitStep(2);
  });
  document.getElementById('visit-back').addEventListener('click', () => showVisitStep(1));
  document.querySelectorAll('.demo-form').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      if (form === visit && !visit.querySelector('[data-visit-step="1"]').hidden) {
        document.getElementById('visit-next').click();
        return;
      }
      if (form === visit) {
        const stepOne = visit.querySelector('[data-visit-step="1"]');
        if ([...stepOne.querySelectorAll('input,select')].some((control) => errorMessage(control))) {
          showVisitStep(1, false);
          validate(stepOne, form);
          return;
        }
      }
      const scope = form === visit ? visit.querySelector('[data-visit-step="2"]') : form;
      if (!validate(scope, form)) return;
      form.querySelector('.form-content').hidden = true;
      const result = form.querySelector('.form-result');
      result.hidden = false;
      result.focus();
    });
    form.addEventListener('reset', () => {
      form.querySelectorAll('input,select,textarea').forEach(clearError);
      form.querySelector('.form-status').textContent = '';
      form.querySelector('.form-content').hidden = false;
      form.querySelector('.form-result').hidden = true;
      if (form === visit) showVisitStep(1, false);
      form.querySelector('input,select').focus();
    });
    form.addEventListener('input', (event) => {
      if (event.target.matches('input,select,textarea') && !errorMessage(event.target)) clearError(event.target);
      if (!form.querySelector('[aria-invalid=true]')) form.querySelector('.form-status').textContent = '';
    });
  });
})();
