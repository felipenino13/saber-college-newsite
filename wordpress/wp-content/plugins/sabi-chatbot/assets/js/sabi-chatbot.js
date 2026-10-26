(function () {
  'use strict';

  function parseConfig(root) {
    const configElement = document.getElementById(root.dataset.sabiConfigId || '');

    if (!configElement) {
      return null;
    }

    try {
      return JSON.parse(configElement.textContent || '{}');
    } catch (error) {
      return null;
    }
  }

  function normalize(value) {
    return String(value || '')
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function initializeChatbot(root) {
    if (root.dataset.sabiInitialized === 'true') {
      return;
    }

    const config = parseConfig(root);
    const launcher = root.querySelector('[data-sabi-toggle]');
    const panel = root.querySelector('[data-sabi-panel]');
    const closeButton = root.querySelector('[data-sabi-close]');
    const announcer = root.querySelector('[data-sabi-announcer]');
    const messages = root.querySelector('[data-sabi-messages]');
    const welcome = root.querySelector('[data-sabi-welcome]');
    const quickReplies = root.querySelector('[data-sabi-quick-replies]');
    const form = root.querySelector('[data-sabi-form]');
    const input = root.querySelector('[data-sabi-input]');
    const sendButton = root.querySelector('[data-sabi-send]');
    const resetButton = root.querySelector('[data-sabi-reset]');
    const notice = root.querySelector('[data-sabi-notice]');
    const privacyLink = root.querySelector('[data-sabi-privacy]');
    const languageButtons = root.querySelectorAll('[data-sabi-language]');
    const demoButtons = root.querySelectorAll('[data-sabi-demo-state]');
    const isEmbedded = root.dataset.sabiEmbedded === 'true';

    if (!config || !launcher || !panel || !closeButton || !messages || !welcome || !quickReplies || !form || !input || !sendButton || !resetButton || !notice || !privacyLink) {
      return;
    }

    root.dataset.sabiInitialized = 'true';

    let language = config.defaultLanguage === 'es' ? 'es' : 'en';
    let languageLocked = false;
    let responseTimer = null;
    let pending = false;
    let leadPending = false;
    let leadStage = '';
    let leadData = {};
    let aiHistory = [];
    let sessionId = createSessionId();
    const avatar = root.querySelector('.sabi-chatbot__avatar');

    function createSessionId() {
      if (window.crypto && typeof window.crypto.randomUUID === 'function') {
        return window.crypto.randomUUID();
      }

      return 'sabi-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
    }

    function ui() {
      return config.ui[language] || config.ui.en;
    }

    function announce(message) {
      if (!announcer) {
        return;
      }

      announcer.textContent = '';
      window.setTimeout(function () {
        announcer.textContent = message;
      }, 20);
    }

    function scrollToLatest() {
      window.requestAnimationFrame(function () {
        messages.scrollTop = messages.scrollHeight;
      });
    }

    function timeLabel() {
      return new Intl.DateTimeFormat(language === 'es' ? 'es-US' : 'en-US', {
        hour: 'numeric',
        minute: '2-digit'
      }).format(new Date());
    }

    function safeUrl(value) {
      try {
        const url = new URL(value, window.location.origin);
        if (url.origin !== window.location.origin || !['http:', 'https:'].includes(url.protocol)) {
          return '';
        }
        return url.href;
      } catch (error) {
        return '';
      }
    }

    function appendMessage(role, text, links) {
      const message = document.createElement('div');
      const bubble = document.createElement('div');
      const paragraph = document.createElement('p');
      const timestamp = document.createElement('time');

      message.className = 'sabi-chatbot__message sabi-chatbot__message--' + role;
      message.dataset.sabiDynamic = 'true';
      bubble.className = 'sabi-chatbot__bubble';
      paragraph.textContent = text;
      timestamp.textContent = timeLabel();

      if (role === 'assistant') {
        const avatarWrap = document.createElement('span');
        avatarWrap.className = 'sabi-chatbot__message-avatar';
        avatarWrap.setAttribute('aria-hidden', 'true');

        if (avatar) {
          const image = document.createElement('img');
          image.src = avatar.currentSrc || avatar.src;
          image.alt = '';
          image.width = 32;
          image.height = 32;
          avatarWrap.appendChild(image);
        }

        message.appendChild(avatarWrap);
      } else {
        message.setAttribute('aria-label', ui().you);
      }

      bubble.appendChild(paragraph);

      if (Array.isArray(links) && links.length) {
        const actions = document.createElement('div');
        actions.className = 'sabi-chatbot__actions';

        links.forEach(function (link) {
          const href = safeUrl(link.url);
          if (!href) {
            return;
          }

          const anchor = document.createElement('a');
          anchor.href = href;
          anchor.textContent = (link.label && link.label[language]) || (link.label && link.label.en) || href;
          actions.appendChild(anchor);
        });

        if (actions.childElementCount) {
          bubble.appendChild(actions);
        }
      }

      bubble.appendChild(timestamp);
      message.appendChild(bubble);
      messages.insertBefore(message, quickReplies);
      scrollToLatest();
    }

    function topicLabel(topicId) {
      const topic = config.topics[topicId];
      if (!topic || !topic.label) {
        return topicId;
      }
      return topic.label[language] || topic.label.en || topicId;
    }

    function renderQuickReplies(topicIds) {
      quickReplies.replaceChildren();

      if (!Array.isArray(topicIds) || !topicIds.length) {
        return;
      }

      const label = document.createElement('p');
      label.textContent = ui().suggestedQuestions;
      quickReplies.appendChild(label);

      const list = document.createElement('div');
      list.className = 'sabi-chatbot__quick-list';

      topicIds.forEach(function (topicId) {
        if (!config.topics[topicId]) {
          return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = topicLabel(topicId);
        button.dataset.sabiTopic = topicId;
        button.addEventListener('click', function () {
          submitMessage(topicLabel(topicId), topicId);
        });
        list.appendChild(button);
      });

      quickReplies.appendChild(list);
    }

    function renderLeadChoices(choices) {
      quickReplies.replaceChildren();
      const list = document.createElement('div');
      list.className = 'sabi-chatbot__quick-list sabi-chatbot__quick-list--lead';

      choices.forEach(function (choice) {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = choice.label;
        button.addEventListener('click', function () {
          submitLeadAnswer(choice.label, choice.value);
        });
        list.appendChild(button);
      });

      quickReplies.appendChild(list);
      scrollToLatest();
    }

    function detectLanguage(value) {
      if (languageLocked) {
        return language;
      }

      const normalized = normalize(value);
      const scores = { en: 0, es: 0 };

      Object.values(config.topics).forEach(function (topic) {
        ['en', 'es'].forEach(function (candidate) {
          (topic.keywords[candidate] || []).forEach(function (keyword) {
            const normalizedKeyword = normalize(keyword);
            if (normalizedKeyword && normalized.includes(normalizedKeyword)) {
              scores[candidate] += normalizedKeyword.length;
            }
          });
        });
      });

      if (/[¿¡ñáéíóú]/i.test(value)) {
        scores.es += 12;
      }

      return scores.es > scores.en ? 'es' : (scores.en > scores.es ? 'en' : language);
    }

    function findTopic(value, detectedLanguage) {
      const normalized = normalize(value);
      let bestTopic = '';
      let bestScore = 0;

      Object.entries(config.topics).forEach(function (entry) {
        const topicId = entry[0];
        const topic = entry[1];

        ['en', 'es'].forEach(function (candidate) {
          (topic.keywords[candidate] || []).forEach(function (keyword) {
            const normalizedKeyword = normalize(keyword);
            if (!normalizedKeyword || !normalized.includes(normalizedKeyword)) {
              return;
            }

            const score = normalizedKeyword.length + (candidate === detectedLanguage ? 6 : 0);
            if (score > bestScore) {
              bestScore = score;
              bestTopic = topicId;
            }
          });
        });
      });

      return bestTopic;
    }

    function syncLanguageControls() {
      root.dataset.sabiLanguage = language;
      languageButtons.forEach(function (button) {
        const isActive = button.dataset.sabiLanguage === language;
        button.classList.toggle('is-active', isActive);
        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
      input.placeholder = ui().inputPlaceholder;
      sendButton.setAttribute('aria-label', ui().send);
      resetButton.textContent = ui().reset;
      notice.textContent = ui().notice;
      privacyLink.textContent = ui().privacy;
      syncLeadInput();
    }

    function syncLeadInput() {
      const placeholders = {
        name: ui().leadNamePrompt,
        email: ui().leadEmailPrompt,
        phone: ui().leadPhonePrompt
      };
      input.placeholder = placeholders[leadStage] || ui().inputPlaceholder;
      input.type = 'text';
      input.inputMode = leadStage === 'phone' ? 'tel' : (leadStage === 'email' ? 'email' : 'text');
      input.autocomplete = leadStage === 'name' ? 'name' : (leadStage === 'email' ? 'email' : (leadStage === 'phone' ? 'tel' : 'off'));
    }

    function resetLeadState() {
      leadStage = '';
      leadData = {};
      leadPending = false;
      syncLeadInput();
    }

    function isAffirmative(value) {
      const answer = normalize(value);
      return ['yes', 'y', 'si', 'sí', 'agree', 'acepto', 'claro', 'ok', 'okay'].includes(answer);
    }

    function isNegative(value) {
      const answer = normalize(value);
      return ['no', 'not now', 'ahora no', 'cancel', 'cancelar', 'no acepto'].includes(answer);
    }

    function cancelLead() {
      resetLeadState();
      appendMessage('assistant', ui().leadCancelled, []);
      renderQuickReplies(config.initialTopics);
      input.focus({ preventScroll: true });
    }

    function askLeadName(message) {
      leadStage = 'name';
      syncLeadInput();
      appendMessage('assistant', message || ui().leadNamePrompt, []);
      renderLeadChoices([{ label: ui().leadCancel, value: 'cancel' }]);
      input.focus({ preventScroll: true });
    }

    function beginLeadOffer() {
      if (!config.leadCapture || !config.leadCapture.enabled) {
        appendMessage('assistant', ui().leadUnavailable, [ui().fallbackLinks[1]]);
        renderQuickReplies(config.initialTopics);
        return;
      }

      leadData = {};
      leadStage = 'offer';
      syncLeadInput();
      renderLeadChoices([
        { label: ui().leadOfferYes, value: 'yes' },
        { label: ui().leadOfferNo, value: 'no' }
      ]);
    }

    function programFrom(value) {
      const answer = normalize(value);
      if (answer === 'professional-nursing' || answer.includes('nursing') || answer.includes('enfermer')) {
        return 'professional-nursing';
      }
      if (answer === 'physical-therapist-assistant' || answer === 'pta' || answer.includes('physical') || answer.includes('terapia')) {
        return 'physical-therapist-assistant';
      }
      if (answer === 'undecided' || answer.includes('not sure') || answer.includes('no se') || answer.includes('no estoy seguro')) {
        return 'undecided';
      }
      return '';
    }

    function consentPrompt() {
      const consent = (config.leadCapture && config.leadCapture.consent && config.leadCapture.consent[language]) || '';
      return ui().leadConsentPrompt.replace('{consent}', consent);
    }

    function processLeadStep(value) {
      if (value === 'cancel') {
        cancelLead();
        return;
      }

      if (leadStage === 'offer') {
        if (value === 'yes' || isAffirmative(value)) {
          askLeadName();
        } else if (value === 'no' || isNegative(value)) {
          cancelLead();
        } else {
          renderLeadChoices([{ label: ui().leadOfferYes, value: 'yes' }, { label: ui().leadOfferNo, value: 'no' }]);
        }
        return;
      }

      if (leadStage === 'name') {
        const name = String(value || '').trim();
        if (name.length < 2 || name.length > 120) {
          askLeadName(ui().leadNameInvalid);
          return;
        }
        leadData.name = name;
        leadStage = 'program';
        syncLeadInput();
        appendMessage('assistant', ui().leadProgramPrompt, []);
        renderLeadChoices([
          { label: ui().programNursing, value: 'professional-nursing' },
          { label: ui().programPta, value: 'physical-therapist-assistant' },
          { label: ui().programUndecided, value: 'undecided' },
          { label: ui().leadCancel, value: 'cancel' }
        ]);
        return;
      }

      if (leadStage === 'program') {
        const program = programFrom(value);
        if (!program) {
          appendMessage('assistant', ui().leadProgramPrompt, []);
          renderLeadChoices([
            { label: ui().programNursing, value: 'professional-nursing' },
            { label: ui().programPta, value: 'physical-therapist-assistant' },
            { label: ui().programUndecided, value: 'undecided' },
            { label: ui().leadCancel, value: 'cancel' }
          ]);
          return;
        }
        leadData.program = program;
        leadStage = 'email';
        syncLeadInput();
        appendMessage('assistant', ui().leadEmailPrompt, []);
        renderLeadChoices([{ label: ui().leadCancel, value: 'cancel' }]);
        input.focus({ preventScroll: true });
        return;
      }

      if (leadStage === 'email') {
        const email = String(value || '').trim();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || email.length > 191) {
          appendMessage('assistant', ui().leadEmailInvalid, []);
          renderLeadChoices([{ label: ui().leadCancel, value: 'cancel' }]);
          return;
        }
        leadData.email = email;
        leadStage = 'phone';
        syncLeadInput();
        appendMessage('assistant', ui().leadPhonePrompt, []);
        renderLeadChoices([{ label: ui().leadCancel, value: 'cancel' }]);
        input.focus({ preventScroll: true });
        return;
      }

      if (leadStage === 'phone') {
        const phone = String(value || '').trim();
        const digits = phone.replace(/\D/g, '');
        if (digits.length < 7 || digits.length > 15) {
          appendMessage('assistant', ui().leadPhoneInvalid, []);
          renderLeadChoices([{ label: ui().leadCancel, value: 'cancel' }]);
          return;
        }
        leadData.phone = phone;
        leadStage = 'consent';
        syncLeadInput();
        appendMessage('assistant', consentPrompt(), []);
        renderLeadChoices([
          { label: ui().leadConsentYes, value: 'yes' },
          { label: ui().leadConsentNo, value: 'no' }
        ]);
        return;
      }

      if (leadStage === 'consent') {
        if (value === 'yes' || isAffirmative(value)) {
          leadData.consent = '1';
          leadStage = 'confirm';
          syncLeadInput();
          appendMessage('assistant', ui().leadConfirmPrompt, []);
          renderLeadChoices([
            { label: ui().leadConfirmYes, value: 'send' },
            { label: ui().leadRestart, value: 'restart' },
            { label: ui().leadCancel, value: 'cancel' }
          ]);
        } else if (value === 'no' || isNegative(value)) {
          cancelLead();
        } else {
          appendMessage('assistant', consentPrompt(), []);
          renderLeadChoices([{ label: ui().leadConsentYes, value: 'yes' }, { label: ui().leadConsentNo, value: 'no' }]);
        }
        return;
      }

      if (leadStage === 'confirm') {
        if (value === 'send' || isAffirmative(value)) {
          submitLead();
        } else if (value === 'restart') {
          leadData = {};
          askLeadName(ui().leadRestarted);
        } else if (value === 'cancel' || isNegative(value)) {
          cancelLead();
        }
      }
    }

    function submitLeadAnswer(displayValue, value) {
      if (!leadStage || leadPending || pending) {
        return;
      }
      appendMessage('user', displayValue, []);
      input.value = '';
      renderQuickReplies([]);
      processLeadStep(value);
    }

    async function submitLead() {
      if (!config.leadCapture || !config.leadCapture.enabled || leadPending || !leadData.consent) {
        return;
      }

      leadPending = true;
      setBusy(true);
      root.dataset.chatState = 'loading';
      renderQuickReplies([]);
      const payload = new FormData();
      Object.entries(leadData).forEach(function (entry) {
        payload.set(entry[0], entry[1]);
      });
      payload.set('action', 'sabi_chatbot_submit_lead');
      payload.set('nonce', config.leadCapture.nonce || '');
      payload.set('language', language);
      payload.set('source_url', window.location.href);

      try {
        const response = await fetch(config.leadCapture.ajaxUrl, {
          method: 'POST',
          body: payload,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
          throw new Error(result && result.data && result.data.message ? result.data.message : ui().fallback);
        }

        const successMessage = result.data && result.data.message ? result.data.message : '';
        resetLeadState();
        appendMessage('assistant', successMessage, []);
        renderQuickReplies(config.initialTopics);
        announce(successMessage);
        input.focus({ preventScroll: true });
      } catch (error) {
        appendMessage('assistant', error instanceof Error ? error.message : ui().fallback, []);
        leadStage = 'confirm';
        renderLeadChoices([
          { label: ui().leadConfirmYes, value: 'send' },
          { label: ui().leadRestart, value: 'restart' },
          { label: ui().leadCancel, value: 'cancel' }
        ]);
      } finally {
        leadPending = false;
        setBusy(false);
      }
    }

    function setBusy(isBusy) {
      pending = isBusy;
      input.disabled = isBusy;
      sendButton.disabled = isBusy;
      messages.setAttribute('aria-busy', isBusy ? 'true' : 'false');
      root.dataset.chatState = isBusy ? 'typing' : 'ready';
    }

    function respond(topicId) {
      const topic = topicId ? config.topics[topicId] : null;
      const answer = topic ? (topic.answer[language] || topic.answer.en) : ui().fallback;
      const links = topic ? topic.links : ui().fallbackLinks;
      const nextTopics = topic ? topic.next : config.initialTopics;

      setBusy(true);
      announce(ui().sabiTyping);
      responseTimer = window.setTimeout(function () {
        appendMessage('assistant', answer, links);
        if (topic && topic.action === 'lead_offer') {
          renderQuickReplies([]);
          beginLeadOffer();
        } else {
          renderQuickReplies(nextTopics);
        }
        setBusy(false);
        announce(answer);
        input.focus({ preventScroll: true });
        responseTimer = null;
      }, Number(config.responseDelay) || 480);
    }

    async function requestAi(message, topicId) {
      if (!config.ai || !config.ai.enabled) {
        respond(topicId);
        return;
      }

      setBusy(true);
      root.dataset.chatState = 'loading';
      announce(ui().sabiTyping);
      const payload = new FormData();
      payload.set('action', 'sabi_chatbot_message');
      payload.set('nonce', config.ai.nonce || '');
      payload.set('message', message);
      payload.set('language', language);
      payload.set('session_id', sessionId);
      payload.set('history', JSON.stringify(aiHistory.slice(-6)));

      try {
        const response = await fetch(config.ai.ajaxUrl, {
          method: 'POST',
          body: payload,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
          throw new Error(result && result.data && result.data.message ? result.data.message : ui().fallback);
        }

        const answer = result.data && result.data.answer ? String(result.data.answer) : ui().fallback;
        const sources = result.data && Array.isArray(result.data.sources) ? result.data.sources : [];
        appendMessage('assistant', answer, sources);
        aiHistory.push({ role: 'user', content: message }, { role: 'assistant', content: answer });
        aiHistory = aiHistory.slice(-6);
        const topic = topicId ? config.topics[topicId] : null;
        renderQuickReplies(topic ? topic.next : config.initialTopics);
        announce(answer);
      } catch (error) {
        const messageText = error instanceof Error ? error.message : ui().fallback;
        appendMessage('assistant', messageText, ui().fallbackLinks);
        renderQuickReplies(config.initialTopics);
        announce(messageText);
      } finally {
        setBusy(false);
        input.focus({ preventScroll: true });
      }
    }

    function submitMessage(value, forcedTopic) {
      const trimmed = String(value || '').trim().slice(0, Number(config.maxInputLength) || 600);
      if (!trimmed || pending) {
        return;
      }

      if (leadStage) {
        submitLeadAnswer(trimmed, trimmed);
        return;
      }

      const detectedLanguage = forcedTopic ? language : detectLanguage(trimmed);
      if (detectedLanguage !== language) {
        language = detectedLanguage;
        syncLanguageControls();
      }

      appendMessage('user', trimmed, []);
      input.value = '';
      renderQuickReplies([]);
      const topicId = forcedTopic || findTopic(trimmed, detectedLanguage);
      const topic = topicId ? config.topics[topicId] : null;
      const requiresControlledFlow = forcedTopic || (topic && ['lead_offer'].includes(topic.action)) || ['privacy', 'historical'].includes(topicId);
      if (requiresControlledFlow || !config.ai || !config.ai.enabled) {
        respond(topicId);
      } else {
        requestAi(trimmed, topicId);
      }
    }

    function resetConversation(nextLanguage, announcement) {
      if (responseTimer) {
        window.clearTimeout(responseTimer);
        responseTimer = null;
      }

      language = nextLanguage === 'es' ? 'es' : 'en';
      setBusy(false);
      messages.querySelectorAll('[data-sabi-dynamic]').forEach(function (message) {
        message.remove();
      });
      const welcomeText = welcome.querySelector('p');
      const welcomeTime = welcome.querySelector('time');
      if (welcomeText) {
        welcomeText.textContent = ui().welcome;
      }
      if (welcomeTime) {
        welcomeTime.textContent = timeLabel();
      }
      input.value = '';
      aiHistory = [];
      sessionId = createSessionId();
      resetLeadState();
      syncLanguageControls();
      renderQuickReplies(config.initialTopics);
      scrollToLatest();
      if (announcement !== null) {
        announce(announcement || ui().resetAnnouncement);
      }
    }

    function openPanel() {
      panel.hidden = false;
      panel.classList.remove('is-opening');
      window.requestAnimationFrame(function () {
        panel.classList.add('is-opening');
      });
      launcher.setAttribute('aria-expanded', 'true');
      announce(language === 'es' ? 'Guia virtual SABI abierta.' : 'SABI virtual guide opened.');
      input.focus({ preventScroll: true });
    }

    function closePanel() {
      if (isEmbedded) {
        return;
      }

      panel.hidden = true;
      panel.classList.remove('is-opening');
      launcher.setAttribute('aria-expanded', 'false');
      announce(language === 'es' ? 'Guia virtual SABI cerrada.' : 'SABI virtual guide closed.');
      launcher.focus({ preventScroll: true });
    }

    launcher.addEventListener('click', function () {
      if (panel.hidden) {
        openPanel();
      } else {
        closePanel();
      }
    });

    closeButton.addEventListener('click', closePanel);

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      submitMessage(input.value, '');
    });

    resetButton.addEventListener('click', function () {
      resetConversation(language, ui().resetAnnouncement);
      input.focus({ preventScroll: true });
    });

    languageButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        const nextLanguage = button.dataset.sabiLanguage === 'es' ? 'es' : 'en';
        if (nextLanguage === language) {
          return;
        }
        languageLocked = true;
        resetConversation(nextLanguage);
        input.focus({ preventScroll: true });
      });
    });

    root.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !panel.hidden) {
        closePanel();
      }
    });

    demoButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        const state = button.dataset.sabiDemoState || 'ready';
        root.dataset.chatState = state;
        demoButtons.forEach(function (item) {
          item.classList.toggle('is-active', item === button);
        });
        announce('Preview state changed to ' + state + '.');
      });
    });

    if (isEmbedded) {
      panel.hidden = false;
      launcher.setAttribute('aria-expanded', 'true');
    }

    resetConversation(language, null);
  }

  function initializeAll() {
    document.querySelectorAll('[data-sabi-chatbot]').forEach(initializeChatbot);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAll);
  } else {
    initializeAll();
  }
})();
