import { ZoomMtg } from '@zoomus/websdk'

/**
 * Zoom Meeting Join via Browser App
 *
 * @type {{init: ZoomMtgApp.init, loader: (function(): HTMLSpanElement), handleJoinMeetingButton: ZoomMtgApp.handleJoinMeetingButton, redirectTo: *, meetingID: (String|string), joinMeeting: ZoomMtgApp.joinMeeting, initSDK: ZoomMtgApp.initSDK, validateBeforeJoining: ZoomMtgApp.validateBeforeJoining, infoContainer: Element, password: *, removeLoader: ZoomMtgApp.removeLoader, eventHandlers: ZoomMtgApp.eventHandlers, generateSignature: (function(): Promise<any>)}}
 */
const ZoomMtgApp = {
  meetingID: atob(zvc_ajx.meeting_id),
  redirectTo: zvc_ajx.redirect_page,
  password: (zvc_ajx.meeting_pwd !== false) ? atob(zvc_ajx.meeting_pwd) : false,
  infoContainer: document.querySelector('.vczapi-zoom-browser-meeting--info__browser'),

  /**
   * Intialize
   */
  init: function () {
    this.initSDK()
    this.eventHandlers()
  },

  /**
   * Initialize the SDK
   */
  initSDK: function () {
    ZoomMtg.setZoomJSLib('https://source.zoom.us/' + zvc_ajx.sdk_version + '/lib', '/av')
    ZoomMtg.preLoadWasm()
    ZoomMtg.prepareWebSDK()
  },

  /**
   * Event Listeners
   */
  eventHandlers: function () {
    let joinMtgButton = document.getElementById('vczapi-zoom-browser-meeting-join-mtg')
    if (joinMtgButton != null) {
      joinMtgButton.onclick = this.handleJoinMeetingButton.bind(this)
    }
  },

  /**
   * HTML loader
   *
   * @returns {HTMLSpanElement}
   */
  loader: function () {
    const loaderWrapper = document.createElement('span')
    loaderWrapper.id = 'zvc-cover'
    return loaderWrapper
  },

  /**
   * Generate the signature for the webSDK
   *
   * @returns {Promise<any>}
   */
  generateSignature: async function () {
    const postData = new FormData()
    postData.append('action', 'get_auth')
    postData.append('noncce', zvc_ajx.zvc_security)
    postData.append('meeting_id', parseInt(this.meetingID))

    const response = await fetch(zvc_ajx.ajaxurl, {
      method: 'POST',
      body: postData
    })

    return response.json()
  },

  /**
   * Remove the loader screen
   */
  removeLoader: function () {
    const cover = document.getElementById('zvc-cover')
    if (cover !== null) {
      document.getElementById('zvc-cover').remove()
    }
  },

  /**
   * Handle join meeting button click
   *
   * @param e
   */
  handleJoinMeetingButton: function (e) {
    e.preventDefault()

    //Show Loader
    document.body.appendChild(this.loader())

    const display_name = document.getElementById('vczapi-jvb-display-name')
    const email = document.getElementById('vczapi-jvb-email')
    const pwd = document.getElementById('meeting_password')

    if (display_name !== null && (display_name.value === null || display_name.value === '')) {
      this.infoContainer.innerHTML = 'Validation: Name is Required!'
      this.infoContainer.style.color = 'red'
      this.removeLoader()
      return false
    }

    //Email Validation
    if (email !== null && (email.value === null || email.value === '')) {
      this.infoContainer.innerHTML = 'Validation: Email is Required!'
      this.infoContainer.style.color = 'red'
      this.removeLoader()
      return false
    }

    //Password Validation
    if (pwd !== null && (pwd.value === null || pwd.value === '')) {
      this.infoContainer.innerHTML = 'Validation: Password is Required!'
      this.infoContainer.style.color = 'red'
      this.removeLoader()
      return false
    }

    if (this.meetingID != null || this.meetingID !== '') {
      this.generateSignature().then(result => {
        if (result.success) {
          document.getElementById('zmmtg-root').style.display = 'block';

          //remove the loader
          this.removeLoader()

          const validatedObjects = {
            name: display_name !== null ? display_name.value : '',
            password: pwd !== null ? pwd.value : '',
            email: email !== null ? email.value : ''
          }
          this.prepBeforeJoin(result, validatedObjects)
        }
      })
    }
  },

  /**
   * Validate the elements before joining the meeting
   *
   * @param response
   * @param validatedObjects
   * @returns {boolean}
   */
  prepBeforeJoin: function (response, validatedObjects) {
    const API_KEY = response.data.key
    const SIGNATURE = response.data.sig
    const REQUEST_TYPE = response.data.type

    //validation complete now remove the main form page and attach zoom screen
    const mainWindow = document.getElementById('vczapi-zoom-browser-meeting')
    if (mainWindow !== null) {
      mainWindow.remove()
    }

    const locale = document.getElementById('meeting_lang')

    //Set this for the additional props to pass before the actual meeting
    const meetConfig = {
      lang: locale !== null ? locale.value : 'en-US',
      leaveUrl: this.redirectTo,
    }

    //Actual meeting join props
    let meetingJoinParams = {
      meetingNumber: parseInt(this.meetingID, 10),
      userName: validatedObjects.name,
      signature: SIGNATURE,
      userEmail: validatedObjects.email,
      passWord: validatedObjects.password ? validatedObjects.password : this.password,
      success: function (res) {
        console.log('Join Meeting Success')
      },
      error: function (res) {
        console.log(res)
      }
    }

    const urlSearchParams = new URLSearchParams(window.location.search)
    const params = Object.fromEntries(urlSearchParams.entries())
    if (params.tk !== null) {
      meetingJoinParams.tk = params.tk
    }

    if (window.location !== window.parent.location) {
      meetConfig.leaveUrl = window.location.href
    }

    if (REQUEST_TYPE === 'jwt') {
      meetingJoinParams.apiKey = API_KEY
    } else if (REQUEST_TYPE === 'sdk') {
      meetingJoinParams.sdkKey = API_KEY
    }

    this.joinMeeting(meetConfig, meetingJoinParams)
  },

  /**
   * Join the meeting finally
   *
   * @param config
   * @param meetingJoinParams
   */
  joinMeeting: function (config, meetingJoinParams) {
    ZoomMtg.init({
      leaveUrl: config.leaveUrl,
      isSupportAV: true,
      meetingInfo: zvc_ajx.meetingInfo,
      disableInvite: zvc_ajx.disableInvite,
      disableRecord: zvc_ajx.disableRecord,
      disableJoinAudio: zvc_ajx.disableJoinAudio,
      isSupportChat: zvc_ajx.isSupportChat,
      isSupportQA: zvc_ajx.isSupportQA,
      isSupportBreakout: zvc_ajx.isSupportBreakout,
      isSupportCC: zvc_ajx.isSupportCC,
      screenShare: zvc_ajx.screenShare,
      success: function () {
        ZoomMtg.i18n.load(config.lang)
        ZoomMtg.i18n.reload(config.lang)

        ZoomMtg.join(meetingJoinParams)
      },
      error: function (res) {
        console.log(res)
      }
    })
  }
}

document.addEventListener('DOMContentLoaded', ZoomMtgApp.init())