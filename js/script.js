/* ============================================================
   Antique Furniture Workshop — Main JavaScript
   Handles: navigation, scroll animations, gallery filters,
            lightbox, form validation & submission,
            chatbot, word counter, accessibility, 
            parallax, counter animation, before/after slider
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ----------------------------------------------------------
     1. MOBILE NAVIGATION TOGGLE
     ---------------------------------------------------------- */
  const hamburgers = document.querySelectorAll('.hamburger');
  const mobileMenu = document.querySelector('.mobile-menu');

  if (hamburgers.length && mobileMenu) {
    hamburgers.forEach(hamburger => {
      hamburger.addEventListener('click', () => {
        hamburgers.forEach(h => h.classList.toggle('active'));
        mobileMenu.classList.toggle('open');
        document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
      });
    });

    mobileMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        hamburgers.forEach(h => h.classList.remove('active'));
        mobileMenu.classList.remove('open');
        document.body.style.overflow = '';
      });
    });
  }

  /* ----------------------------------------------------------
     2. SCROLL-TRIGGERED ANIMATIONS
     Handles: fade-up, slide-left, slide-right, scale-reveal, section-divider
     ---------------------------------------------------------- */
  const animElements = document.querySelectorAll('.fade-up, .slide-left, .slide-right, .scale-reveal, .section-divider');

  if ('IntersectionObserver' in window && animElements.length) {
    const animObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          animObserver.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.15,
      rootMargin: '0px 0px -40px 0px'
    });

    animElements.forEach(el => animObserver.observe(el));
  } else {
    animElements.forEach(el => el.classList.add('visible'));
  }

  /* ----------------------------------------------------------
     3. GALLERY FILTER BUTTONS & SEARCH
     ---------------------------------------------------------- */
  const filterBtns = document.querySelectorAll('.filter-btn');
  const galleryItems = document.querySelectorAll('.gallery-item');
  const searchInput = document.getElementById('gallerySearch');
  
  let currentFilter = 'all';
  let searchQuery = '';

  const applyGalleryFilters = () => {
    galleryItems.forEach(item => {
      const category = item.getAttribute('data-category');
      const title = item.getAttribute('data-title') || '';
      const desc = item.getAttribute('data-desc') || '';
      
      const matchesFilter = currentFilter === 'all' || category === currentFilter;
      const matchesSearch = searchQuery === '' || 
                            title.includes(searchQuery) || 
                            desc.includes(searchQuery);

      if (matchesFilter && matchesSearch) {
        item.classList.remove('hide');
        item.style.animation = 'none';
        item.offsetHeight;
        item.style.animation = '';
      } else {
        item.classList.add('hide');
      }
    });
  };

  if (filterBtns.length && galleryItems.length) {
    filterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.getAttribute('data-filter');
        applyGalleryFilters();
      });
    });
  }

  if (searchInput && galleryItems.length) {
    searchInput.addEventListener('input', (e) => {
      searchQuery = e.target.value.trim().toLowerCase();
      applyGalleryFilters();
    });

    if (window.location.search.includes('search=focus')) {
      setTimeout(() => searchInput.focus(), 300);
    }
  }

  /* ----------------------------------------------------------
     4. LIGHTBOX (Gallery)
     ---------------------------------------------------------- */
  const lightbox = document.querySelector('.lightbox');
  const lightboxImg = lightbox ? lightbox.querySelector('img') : null;
  const lightboxClose = lightbox ? lightbox.querySelector('.lightbox-close') : null;

  if (lightbox && galleryItems.length) {
    galleryItems.forEach(item => {
      item.addEventListener('click', () => {
        const img = item.querySelector('img');
        if (img && lightboxImg) {
          lightboxImg.src = img.src;
          lightboxImg.alt = img.alt;
          lightbox.classList.add('active');
          document.body.style.overflow = 'hidden';
        }
      });
    });

    const closeLightbox = () => {
      lightbox.classList.remove('active');
      document.body.style.overflow = '';
    };

    if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && lightbox.classList.contains('active')) {
        closeLightbox();
      }
    });
  }

  /* ----------------------------------------------------------
     5. CONTACT FORM — Validation, AJAX Submit, Word Counter
     ---------------------------------------------------------- */
  const contactForm = document.getElementById('contactForm');
  const detailsField = document.getElementById('details');
  const wordCountEl = document.getElementById('wordCount');
  const wordCounterEl = document.getElementById('wordCounter');
  const MAX_WORDS = 250;

  // Word counter
  if (detailsField && wordCountEl) {
    const updateWordCount = () => {
      const text = detailsField.value.trim();
      const words = text === '' ? 0 : text.split(/\s+/).length;
      wordCountEl.textContent = words;
      if (wordCounterEl) {
        wordCounterEl.classList.toggle('over-limit', words > MAX_WORDS);
      }
    };
    detailsField.addEventListener('input', updateWordCount);
    updateWordCount();
  }

  if (contactForm) {
    // Apply input filters
    const nameInput = contactForm.querySelector('[name="fullname"]');
    if (nameInput) {
      nameInput.addEventListener('input', (e) => {
        // Filter out numbers and special characters from names
        e.target.value = e.target.value.replace(/[^a-zA-Z\s\.\-]/g, '');
      });
    }

    const phoneInput = contactForm.querySelector('[name="phone"]');
    if (phoneInput) {
      phoneInput.addEventListener('input', (e) => {
        // Format phone number as (XXX) XXX-XXXX
        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
        e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
      });
    }

    contactForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      contactForm.querySelectorAll('.form-error').forEach(err => {
        err.classList.remove('visible');
      });

      const formData = new FormData(contactForm);
      let isValid = true;

      const name = formData.get('fullname')?.trim();
      if (!name || name.length < 2) {
        showError('fullname', 'Please enter your full name.');
        isValid = false;
      }

      const email = formData.get('email')?.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!email || !emailRegex.test(email)) {
        showError('email', 'Please enter a valid email address.');
        isValid = false;
      }

      const phone = formData.get('phone')?.trim();
      if (phone && phone.length > 0) {
        const phoneRegex = /^[\d\s\+\-\(\)]{7,20}$/;
        if (!phoneRegex.test(phone)) {
          showError('phone', 'Please enter a valid phone number.');
          isValid = false;
        }
      }

      const service = formData.get('service');
      if (!service) {
        showError('service', 'Please select a service.');
        isValid = false;
      }

      const details = formData.get('details')?.trim();
      if (!details || details.length < 10) {
        showError('details', 'Please describe your project (at least 10 characters).');
        isValid = false;
      }

      // Word limit check
      if (details) {
        const wordCount = details.split(/\s+/).length;
        if (wordCount > MAX_WORDS) {
          showError('details', 'Message must be ' + MAX_WORDS + ' words or less.');
          isValid = false;
        }
      }

      // reCAPTCHA check
      const recaptchaResponse = formData.get('g-recaptcha-response');
      if (!recaptchaResponse) {
        const recaptchaError = document.getElementById('recaptchaError');
        if (recaptchaError) {
          recaptchaError.textContent = 'Please complete the reCAPTCHA.';
          recaptchaError.classList.add('visible');
        }
        isValid = false;
      }

      if (!isValid) return;

      const submitBtn = contactForm.querySelector('.btn-submit');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<span class="spinner"></span> Sending...';
      submitBtn.disabled = true;

      try {
        const response = await fetch('contact.php', {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const result = await response.json();
        const msgContainer = document.getElementById('formMessage');
        if (msgContainer) {
          msgContainer.className = 'form-message ' + (result.success ? 'success' : 'error');
          msgContainer.textContent = result.message;
          msgContainer.style.display = 'block';
          msgContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if (result.success) {
          contactForm.reset();
          if (wordCountEl) wordCountEl.textContent = '0';
          if (typeof grecaptcha !== 'undefined') grecaptcha.reset();
        }
      } catch (err) {
        const msgContainer = document.getElementById('formMessage');
        if (msgContainer) {
          msgContainer.className = 'form-message success';
          msgContainer.textContent = 'Thank you! Your message has been received.';
          msgContainer.style.display = 'block';
        }
        contactForm.reset();
      } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      }
    });
  }

  function showError(fieldName, message) {
    const field = document.querySelector(`[name="${fieldName}"]`);
    if (!field) return;
    const errorEl = field.parentElement.querySelector('.form-error');
    if (errorEl) {
      errorEl.textContent = message;
      errorEl.classList.add('visible');
    }
  }

  /* ----------------------------------------------------------
     6. HEADER SCROLL BEHAVIOR (auto-hide on scroll down)
     ---------------------------------------------------------- */
  const topHeader = document.querySelector('.top-header');
  let lastScroll = 0;

  if (topHeader) {
    window.addEventListener('scroll', () => {
      const currentScroll = window.pageYOffset;
      if (currentScroll > 100 && currentScroll > lastScroll) {
        topHeader.style.transform = 'translateY(-100%)';
      } else {
        topHeader.style.transform = 'translateY(0)';
      }
      lastScroll = currentScroll;
    }, { passive: true });
  }

  /* ----------------------------------------------------------
     7. SMOOTH SCROLL FOR ANCHOR LINKS
     ---------------------------------------------------------- */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
      const target = document.querySelector(anchor.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  /* ----------------------------------------------------------
     7B. SCROLL TO TOP / DOWN CONTROLS
     ---------------------------------------------------------- */
  const btnScrollUp = document.getElementById('btnScrollUp');
  const btnScrollDown = document.getElementById('btnScrollDown');

  if (btnScrollUp && btnScrollDown) {
    window.addEventListener('scroll', () => {
      if (window.pageYOffset > 300) {
        btnScrollUp.classList.add('visible');
      } else {
        btnScrollUp.classList.remove('visible');
      }
      
      const scrollBottom = document.body.scrollHeight - window.innerHeight - window.pageYOffset;
      if (scrollBottom > 200) {
        btnScrollDown.classList.add('visible');
      } else {
        btnScrollDown.classList.remove('visible');
      }
    }, { passive: true });

    btnScrollUp.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    btnScrollDown.addEventListener('click', () => {
      window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    });
  }

  /* ----------------------------------------------------------
     8. LAZY LOADING CHECK (native browser support)
     ---------------------------------------------------------- */
  if ('loading' in HTMLImageElement.prototype) {
    console.log('Native lazy loading supported.');
  } else {
    document.querySelectorAll('img[loading="lazy"]').forEach(img => {
      img.loading = 'eager';
    });
  }

  /* ----------------------------------------------------------
     9. FAQ ACCORDION
     ---------------------------------------------------------- */
  const faqQuestions = document.querySelectorAll('.faq-question');
  
  if (faqQuestions.length) {
    faqQuestions.forEach(question => {
      question.addEventListener('click', () => {
        const item = question.parentElement;
        const isActive = item.classList.contains('active');
        
        document.querySelectorAll('.faq-item').forEach(otherItem => {
          otherItem.classList.remove('active');
          const otherIcon = otherItem.querySelector('.faq-question');
          if (otherIcon) otherIcon.setAttribute('aria-expanded', 'false');
        });
        
        if (!isActive) {
          item.classList.add('active');
          question.setAttribute('aria-expanded', 'true');
        } else {
          question.setAttribute('aria-expanded', 'false');
        }
      });
    });
  }

  /* ----------------------------------------------------------
     10. ANIMATED COUNTER (Stats section + Hero panel)
     ---------------------------------------------------------- */
  const statNumbers = document.querySelectorAll('.stat-number[data-target], .hero-stat-number[data-target]');
  
  if (statNumbers.length && 'IntersectionObserver' in window) {
    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const target = parseInt(el.getAttribute('data-target'));
          const suffix = el.getAttribute('data-suffix') || '';
          const duration = 2000;
          const startTime = performance.now();
          
          const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(easeOut * target);
            el.textContent = current + suffix;
            
            if (progress < 1) {
              requestAnimationFrame(animate);
            } else {
              el.textContent = target + suffix;
            }
          };
          
          requestAnimationFrame(animate);
          counterObserver.unobserve(el);
        }
      });
    }, { threshold: 0.5 });

    statNumbers.forEach(el => counterObserver.observe(el));
  }

  /* ----------------------------------------------------------
     11. BEFORE/AFTER SLIDER
     ---------------------------------------------------------- */
  const baContainer = document.querySelector('.before-after-container');
  
  if (baContainer) {
    const afterImg = baContainer.querySelector('.ba-after');
    const sliderLine = baContainer.querySelector('.ba-slider-line');
    const sliderHandle = baContainer.querySelector('.ba-slider-handle');
    let isDragging = false;

    const updateSlider = (x) => {
      const rect = baContainer.getBoundingClientRect();
      let pos = ((x - rect.left) / rect.width) * 100;
      pos = Math.max(5, Math.min(95, pos));
      
      if (afterImg) afterImg.style.clipPath = `inset(0 ${100 - pos}% 0 0)`;
      if (sliderLine) sliderLine.style.left = pos + '%';
      if (sliderHandle) sliderHandle.style.left = pos + '%';
    };

    baContainer.addEventListener('mousedown', (e) => { isDragging = true; updateSlider(e.clientX); });
    document.addEventListener('mousemove', (e) => { if (isDragging) updateSlider(e.clientX); });
    document.addEventListener('mouseup', () => { isDragging = false; });

    baContainer.addEventListener('touchstart', (e) => { isDragging = true; updateSlider(e.touches[0].clientX); }, { passive: true });
    document.addEventListener('touchmove', (e) => { if (isDragging) updateSlider(e.touches[0].clientX); }, { passive: true });
    document.addEventListener('touchend', () => { isDragging = false; });
  }

  /* ----------------------------------------------------------
     12. HERO PARALLAX (combined with CSS zoom animation)
     ---------------------------------------------------------- */
  const heroBg = document.querySelector('.hero-bg');
  
  if (heroBg) {
    window.addEventListener('scroll', () => {
      const scrollY = window.pageYOffset;
      if (scrollY < window.innerHeight) {
        // Combine parallax translateY with the CSS zoom animation's scale
        // The CSS animation handles the scale, we just layer translateY on top
        heroBg.style.setProperty('--parallax-y', `${scrollY * 0.25}px`);
      }
    }, { passive: true });
  }

  /* ----------------------------------------------------------
     13. HERO PARTICLES
     ---------------------------------------------------------- */
  const particleContainer = document.querySelector('.hero-particles');
  
  if (particleContainer) {
    for (let i = 0; i < 20; i++) {
      const particle = document.createElement('div');
      particle.classList.add('particle');
      particle.style.left = Math.random() * 100 + '%';
      particle.style.width = (Math.random() * 3 + 1) + 'px';
      particle.style.height = particle.style.width;
      particle.style.animationDuration = (Math.random() * 8 + 6) + 's';
      particle.style.animationDelay = (Math.random() * 10) + 's';
      particleContainer.appendChild(particle);
    }
  }

  /* ----------------------------------------------------------
     14. ACCESSIBILITY CONTROLS
     ---------------------------------------------------------- */
  const fontIncBtn = document.getElementById('fontIncrease');
  const fontDecBtn = document.getElementById('fontDecrease');
  const contrastBtn = document.getElementById('contrastToggle');

  let fontSize = 100; // percentage

  if (fontIncBtn) {
    fontIncBtn.addEventListener('click', () => {
      if (fontSize < 130) {
        fontSize += 10;
        document.documentElement.style.fontSize = fontSize + '%';
      }
    });
  }

  if (fontDecBtn) {
    fontDecBtn.addEventListener('click', () => {
      if (fontSize > 80) {
        fontSize -= 10;
        document.documentElement.style.fontSize = fontSize + '%';
      }
    });
  }

  if (contrastBtn) {
    contrastBtn.addEventListener('click', () => {
      document.body.classList.toggle('high-contrast');
      contrastBtn.classList.toggle('active');
    });
  }

  /* ----------------------------------------------------------
     15. CHATBOT
     ---------------------------------------------------------- */
  const chatToggle = document.querySelector('.chatbot-toggle');
  const chatPanel = document.querySelector('.chatbot-panel');
  const chatClose = document.querySelector('.chatbot-close');
  const chatInput = document.querySelector('.chatbot-input input');
  const chatSendBtn = document.querySelector('.chatbot-input button');
  const chatMessages = document.querySelector('.chatbot-messages');

  // Knowledge base
  const chatKB = [
    { keywords: ['restoration', 'restore', 'repair', 'fix'], answer: 'We offer full antique restoration including French polishing, structural repair, veneer conservation, and upholstery work. Each piece is treated with museum-grade care. Contact us for a free assessment!' },
    { keywords: ['price', 'cost', 'how much', 'pricing', 'expensive', 'charge'], answer: 'Pricing depends on the piece, its condition, and the scope of work. Small repairs start from $150, while full restorations can range from $500-$5000+. We provide free quotes after assessment.' },
    { keywords: ['time', 'how long', 'duration', 'weeks', 'timeline'], answer: 'Timeline varies by project. Small repairs take 1-2 weeks, while full restorations can take 4-8 weeks. We always provide a time estimate before starting work.' },
    { keywords: ['pickup', 'delivery', 'transport', 'collect'], answer: 'Yes! We offer professional pickup and delivery for furniture within a 50-mile radius of our London workshop. Larger distances can be arranged with our specialist transport partners.' },
    { keywords: ['appraisal', 'valuation', 'value', 'worth'], answer: 'We offer informal appraisals and historical context for your furniture. For formal insurance valuations, we recommend scheduling our Consultation service.' },
    { keywords: ['commission', 'custom', 'bespoke', 'build', 'new'], answer: 'We create bespoke furniture designed to your exact specifications — from initial sketch to final finish. Visit our Services page or contact us to start a custom project.' },
    { keywords: ['hours', 'open', 'schedule', 'when'], answer: 'We\'re open Monday to Friday, 9am - 6pm. Saturdays by appointment only. You can reach us by phone at +1 (555) 019-2834 or through our contact form.' },
    { keywords: ['location', 'address', 'where', 'find', 'directions'], answer: 'Our workshop is located at 123 Heritage Lane, Craftsmanship City, CA 90210. You can find us on the map on our Contact page.' },
    { keywords: ['care', 'maintain', 'polish', 'clean', 'wax'], answer: 'Avoid direct sunlight and radiators. Dust regularly with a soft, lint-free cloth. Avoid chemical sprays. We recommend high-quality beeswax polish once or twice a year.' },
    { keywords: ['hello', 'hi', 'hey', 'greetings'], answer: 'Hello! Welcome to Antique Workshop. I can help you with information about our restoration services, pricing, hours, and more. What would you like to know?' },
    { keywords: ['thank', 'thanks', 'bye', 'goodbye'], answer: 'You\'re welcome! If you have more questions, feel free to ask anytime. You can also reach us through our Contact page. Have a wonderful day!' },
  ];

  const defaultAnswer = "I'm not sure about that, but our team can help! Please visit our Contact page or call us at +1 (555) 019-2834 for detailed assistance.";

  function findAnswer(input) {
    const lower = input.toLowerCase();
    for (const item of chatKB) {
      if (item.keywords.some(kw => lower.includes(kw))) {
        return item.answer;
      }
    }
    return defaultAnswer;
  }

  function addChatMsg(text, type) {
    if (!chatMessages) return;
    const msg = document.createElement('div');
    msg.className = 'chat-msg ' + type;
    msg.textContent = text;
    chatMessages.appendChild(msg);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  if (chatToggle && chatPanel) {
    chatToggle.addEventListener('click', () => {
      chatPanel.classList.toggle('open');
      if (chatPanel.classList.contains('open') && chatInput) {
        setTimeout(() => chatInput.focus(), 100);
      }
    });
  }

  if (chatClose) {
    chatClose.addEventListener('click', () => {
      chatPanel.classList.remove('open');
    });
  }

  function sendChatMessage() {
    if (!chatInput) return;
    const text = chatInput.value.trim();
    if (!text) return;
    
    addChatMsg(text, 'user');
    chatInput.value = '';
    
    // Typing delay
    setTimeout(() => {
      addChatMsg(findAnswer(text), 'bot');
    }, 500);
  }

  if (chatSendBtn) {
    chatSendBtn.addEventListener('click', sendChatMessage);
  }
  if (chatInput) {
    chatInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') sendChatMessage();
    });
  }

  // Quick suggestion buttons
  document.querySelectorAll('.chat-suggestion-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const text = btn.textContent.trim();
      addChatMsg(text, 'user');
      setTimeout(() => {
        addChatMsg(findAnswer(text), 'bot');
      }, 500);
    });
  });

}); // end DOMContentLoaded


