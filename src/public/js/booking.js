import flatpickr from 'flatpickr'

(function () {
  const vczapiBooking = (container, flatpickrOptions = {}) => {
    if (!container || !(container instanceof Element)) {
      throw new Error('vczapiBooking expects a container element.')
    }

    // Re-init guard per container
    if (container.dataset.vczapiInitialized === '1') {
      return container.vczapiInstance
    }

    const globalParams = vczapiMeetingBookerParams ?? { ajaxURL: '' }

    // Internal state
    const DOM = {
      container: null, form: null, dateInput: null, // BEM-friendly: e.g., .vczapi-booking__date
    }

    const SELECTORS = {
      form: 'form', dateInput: '.vczapi-meeting-booker__date-input',
      loader: '.vczapi-meeting-booker__loader',
      formSubmittingToggle: 'vczapi-meeting-booker__loader--show',
    }

    let fp = null
    let options = {
      enableTime: true,
      inline: true,
      defaultDate: new Date(),
      minuteIncrement: 1, // Sets the minute step to 1
      hourIncrement: 1 // Sets the hour step to 1
    }

    options = { ...options, ...flatpickrOptions }

    function showLoader () {
      DOM.loader.classList.add(SELECTORS.formSubmittingToggle)
    }

    function hideLoader () {
      DOM.loader.classList.remove(SELECTORS.formSubmittingToggle)
    }

    function initFlatpickr () {
      if (!DOM.dateInput) {
        console.error('VCZAPI Booking Form: No Date input provided.')
        return
      }
      if (DOM.dateInput._flatpickr && typeof DOM.dateInput._flatpickr.destroy === 'function') {
        DOM.dateInput._flatpickr.destroy()
      }
      fp = flatpickr(DOM.dateInput, options)
    }

    function onSubmit (e) {
      e.preventDefault()
      e.stopPropagation()

      showLoader()
      const formData = new FormData(DOM.form)

      // Log form data entries
      for (const [key, value] of formData.entries()) {
        console.log(`Form Data: ${key} = ${value}`)
      }

      //Validation
      // Check honeypot
      if (formData.get('zoom_action')) {
        hideLoader()
        console.error('Honeypot triggered')
        return
      }

      // Validate required fields
      if (!formData.get('vczapi-meeting-booker__name') || !formData.get('vczapi-meeting-booker__date-input')) {
        hideLoader()
        console.error('Required fields missing')
        return
      }
      //End Validation

      formData.append('action', 'vczapi-meeting-booker-new-booking')

      fetch(globalParams.ajaxURL, {
        method: 'POST',
        body: formData,
      }).then(response => response.json()).then(data => {
        console.log('Success:', data)
      }).catch(error => {
        console.error('Error:', error)
      }).finally(() => {
        hideLoader()
      })
    }

    function cacheDOM () {
      DOM.container = container
      DOM.form = container.querySelector('form') || null
      DOM.dateInput = container.querySelector(SELECTORS.dateInput) || null
      DOM.loader = container.querySelector(SELECTORS.loader) || null
      console.log(DOM)
    }

    function eventListeners () {
      if (DOM.form) DOM.form.addEventListener('submit', onSubmit)
    }

    function init () {
      cacheDOM()
      initFlatpickr()
      eventListeners()
      container.dataset.vczapiInitialized = '1'
      container.vczapiInstance = instance
      return instance
    }

    function destroy () {
      // Remove any listeners you add in eventListeners()
      if (fp && typeof fp.destroy === 'function') {
        fp.destroy()
      }
      fp = null
      delete container.dataset.vczapiInitialized
      delete container.vczapiInstance
    }

    function reinit (newFlatpickrOptions = {}) {
      destroy()
      options = { ...newFlatpickrOptions }
      return init()
    }

    const instance = {
      init, destroy, reinit, getContainer () {
        return DOM.container
      }, getForm () {
        return DOM.form
      }, getInput () {
        return DOM.dateInput
      }, getFlatpickr () {
        return fp
      },
    }

    // Auto-init for simplicity
    return init()
  }

  const createVczapiBooking = (selector, flatpickrOptions = {}) => {
    if (!selector) {
      console.warn('No selector provided')
      return []
    }
    const containers = document.querySelectorAll(selector)
    return Array.from(containers).map((el) => vczapiBooking(el, flatpickrOptions))
  }

  // Expose for consumers following the same BEM structure
  window.createVczapiBooking = createVczapiBooking

  document.addEventListener('DOMContentLoaded', function () {
    const instances = createVczapiBooking('.vczapi-meeting-booker')
    console.log(instances)
  })
})()