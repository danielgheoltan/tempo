import { easepick } from '@easepick/core';
import { DateTime } from '@easepick/datetime';
import { PresetPlugin } from '@easepick/preset-plugin';
import { RangePlugin } from '@easepick/range-plugin';

Number.prototype.fromSecondsToHoursMinutesFormat = function () {
    const hours = Math.floor(this / 3600),
          minutes = Math.floor((this % 3600) / 60);

    return hours.toString() + ':' + minutes.toString().padStart(2, '0');
};

String.prototype.toSeconds = function () {
    // Split the time string by ':' to separate hours and minutes
    const [a, b] = this.split(':');

    // Convert hours and minutes to numbers
    const hoursNumber = parseInt(a, 10) || 0;

    let minutesNumber = parseInt(b, 10) || 0;

    if (b && (b.length < 2)) {
        minutesNumber *= 10;
    }

    return hoursNumber * 60 * 60 + minutesNumber * 60;
};

DateTime.prototype.firstDayOfPreviousMonth = function () {
    // Clone this DateTime object representing the current date
    let dt = this.clone();

    // Set the month to the previous month
    dt.setMonth(dt.getMonth() - 1);

    // Set the date to 1, which gives the first day of the month
    dt.setDate(1);

    return dt;
};

DateTime.prototype.lastDayOfPreviousMonth = function () {
    // Clone this DateTime object representing the current date
    let dt = this.clone();

    // Set the date to 0, which gives the last day of the previous month
    dt.setDate(0);

    return dt;
};

DateTime.prototype.firstDayOfCurrentMonth = function () {
    // Clone this DateTime object representing the current date
    let dt = this.clone();

    // Set the date to 1, which gives the first day of the month
    dt.setDate(1);

    return dt;
};

DateTime.prototype.lastDayOfCurrentMonth = function () {
    // Clone this DateTime object representing the current date
    let dt = this.clone();

    // Get month of the current date
    const month = dt.getMonth();

    // Set the month to the next month
    dt.setMonth(month + 1);

    // Set the date to 0, which gives the last day of the current month
    dt.setDate(0);

    return dt;
};

DateTime.prototype.getWeekFirstDate = function () {
    // Clone this DateTime object representing the current date
    let dt = this.clone();

    // Get the current day of the week (0 for Sunday, 1 for Monday, ..., 6 for Saturday)
    let currentDayOfWeek = dt.getDay();

    // Calculate the number of days to subtract to get to the first day of the week (Monday)
    let daysToSubtract = currentDayOfWeek === 0 ? 6 : currentDayOfWeek - 1;

    // Subtract the number of days from the current date to get to the first day of the week
    dt.subtract(daysToSubtract, 'day');

    return dt;
};

DateTime.prototype.getWeekLastDate = function () {
    // Clone this DateTime object representing the current date
    let dt = this.clone();

    // Get the current day of the week (0 for Sunday, 1 for Monday, ..., 6 for Saturday)
    let currentDayOfWeek = dt.getDay();

    // Calculate the number of days remaining until the end of the week (Sunday)
    let daysToEndOfWeek = (7 - currentDayOfWeek) % 7;

    // Add the number of days to the current date to get to the last day of the week (Saturday)
    dt.add(daysToEndOfWeek, 'day');

    return dt;
};

window.APP = {
    calendarPicker: null,

    DB: {
        deleteUrl: 'work-log/db/delete.php',
        saveUrl: 'work-log/db/save.php'
    },

    JIRA: {
        saveUrl: 'work-log/jira/save.php'
    },

    /**
     * @param {String} cookieName
     */
    getCookie: function (cookieName) {
        // Create a regular expression to match the cookie with the specified name
        const cookieRegExp = new RegExp(`${cookieName}\\s*=\\s*([^;]+)`);

        // Use the regular expression to directly extract the value of the cookie
        const match = document.cookie.match(cookieRegExp);

        // If the cookie with the specified name is found, return its value; otherwise, return null
        return match ? decodeURIComponent(match[1]) : null;
    },

    handleCmdDown: function () {
        document.body.classList.add('cmd-pressed');
        document.body.cmdPressed = true;
    },

    handleCmdUp: function () {
        document.body.classList.remove('cmd-pressed');
        document.body.cmdPressed = false;
    },

    /**
     * @param {Event} event
     */
    onkey: function (event) {
        if (event.type === 'blur') {
            this.handleCmdUp();
            return;
        }

        if (event.key === 'Control' || event.key === 'Meta') {
            if (event.repeat) {
                event.preventDefault();
                return;
            }

            if (event.type === 'keydown') {
                this.handleCmdDown();
            } else if (event.type === 'keyup') {
                this.handleCmdUp();
            }
        }
    },

    /**
     * @param {String} text
     */
    translate: function (text) {
        if (APP.translations[text] && APP.translations[text][APP.language]) {
            return APP.translations[text][APP.language];
        }

        return text;
    },

    init: function () {
        this.initEvents();
        this.initHeader();
        this.initCalendars();
    },

    initEvents: function () {
        if (this.JIRA.baseUrl) {
            window.addEventListener('keydown', this.onkey.bind(this));
            window.addEventListener('keyup', this.onkey.bind(this));
            window.addEventListener('blur', this.onkey.bind(this));
        }
    },

    initHeader: function () {
        if (window.innerWidth < 1280) {
            const pageHeader = document.getElementById('page-header');

            document.body.style.marginTop = pageHeader.offsetHeight + 'px';
            pageHeader.style.position = 'fixed';
        }
    },

    initCalendars: function () {
        const langCookie = this.getCookie('easepick-lang') || 'en-US',
              beginDateTimeCookie = this.getCookie('easepick-begin-datetime'),
              endDateTimeCookie = this.getCookie('easepick-end-datetime');

        const pickerForm = document.getElementById('datepicker-form'),
              pickerIcon = document.getElementById('datepicker-icon');

        const dt = new DateTime();

        const i18n = {
            'en-US': ['Today', 'Yesterday', 'This Week', 'Previous Week', 'This Month', 'Previous Month'],
            'ro-RO': ['Astăzi', 'Ieri', 'Săptămâna curentă', 'Săptămâna trecută', 'Luna curentă', 'Luna trecută']
        };

        const customPresetLabels = i18n[langCookie] || i18n['en-US'];

        this.calendarPicker = new easepick.create({
            element: '#datepicker',
            css: [
                'css/easepick-customize.min.css'
            ],
            lang: langCookie,
            PresetPlugin: {
                position: 'left',
                customPreset: {
                    [customPresetLabels[0]]: [dt.clone(), dt.clone()],
                    [customPresetLabels[1]]: [dt.clone().subtract(1, 'day'), dt.clone()],
                    [customPresetLabels[2]]: [dt.getWeekFirstDate(), dt.getWeekLastDate()],
                    [customPresetLabels[3]]: [dt.clone().subtract(7, 'days').getWeekFirstDate(), dt.clone().subtract(7, 'days').getWeekLastDate()],
                    [customPresetLabels[4]]: [dt.clone().firstDayOfCurrentMonth(), dt.clone().lastDayOfCurrentMonth()],
                    [customPresetLabels[5]]: [dt.firstDayOfPreviousMonth(), dt.lastDayOfPreviousMonth()]
                }
            },
            RangePlugin: {
                tooltip: false
            },
            plugins: [
                PresetPlugin,
                RangePlugin
            ],
            setup (picker) {
                picker.on('show', (e) => {
                    pickerIcon.classList.add('active');
                });

                picker.on('hide', (e) => {
                    pickerIcon.classList.remove('active');
                });

                picker.on('select', (e) => {
                    pickerForm.submit();
                });

                if (beginDateTimeCookie) {
                    picker.setStartDate(beginDateTimeCookie);
                }

                if (endDateTimeCookie) {
                    picker.setEndDate(endDateTimeCookie);
                }
            }
        });
    },

    /**
     * @param {HTMLElement} tableElement
     */
    addLog: function (tableElement) {
        const dataStarted = tableElement.dataset.started,
              tbodyElement = tableElement.querySelector('tbody');

        if (tbodyElement) {
            const template = document.getElementById('log-template'),
                  content = template.content.cloneNode(true);

            content.querySelector('[data-started]').dataset.started = dataStarted;
            tbodyElement.appendChild(content);
        }
    },

    Input: {
        /**
         * @param {Event} event
         */
        onclick: function (event) {
            const element = event.currentTarget;

            if (document.body.cmdPressed &&
                APP.JIRA.baseUrl &&
                element.dataset.name &&
                element.dataset.name === 'issue_key' &&
                element.value
            ) {
                window.open(APP.JIRA.baseUrl + '/browse/' + element.value, '_blank');
                element.blur();
                return;
            } else {
                this.select(element);
            }
        },

        /**
         * @param {HTMLElement} element
         */
        onfocus: function (element) {
            this.select(element);
        },

        /**
         * @param {HTMLElement} element
         */
        onblur: function (element) {
            if (element.readOnly) {
                window.getSelection().removeAllRanges();
            } else if (element.value !== element.dataset.value) {
                this.onchange(element);
            }
        },

        /**
         * @param {HTMLElement} element
         */
        onchange: function (element) {
            if (typeof element.dataset.value !== 'undefined') {
                element.dataset.value = element.value;
                element.setAttribute('value', element.value);
            }
            this.saveToDB(element);
        },

        /**
         * @param {HTMLElement} element
         */
        select: function (element) {
            if (element.readOnly || element.dataset.name === 'time_spent') {
                setTimeout(function () {
                    element.select();
                }, 0);
            }
        },

        /**
         * @param {HTMLElement} element
         */
        saveToDB: function (element) {
            const rowElement = element.closest('tr'),
                  tableElement = rowElement.closest('table'),
                  isTimeSpent = element.dataset.name === 'time_spent';

            if (isTimeSpent) {
                tableElement.classList.add('loading-db');
            }

            rowElement.classList.add('loading-db');

            APP.fetch(APP.DB.saveUrl, {
                index: rowElement.dataset.index,
                [element.dataset.name]: element.value,
                started: rowElement.dataset.started,
                synced: '0'
            })
            .then((response) => {
                if (response.index) {
                    rowElement.dataset.index = response.index;

                    if (isTimeSpent) {
                        let totalSpentTimeValueSeconds = 0;
                        tableElement.querySelectorAll('[data-name="time_spent"]').forEach((e) => {
                            totalSpentTimeValueSeconds += e.value.toSeconds();
                        });

                        const totalSpentTimeElement = tableElement.querySelector('[data-name="total_time_spent"]');
                        totalSpentTimeElement.innerText = totalSpentTimeValueSeconds.fromSecondsToHoursMinutesFormat();
                        tableElement.dataset.fulfilled = (totalSpentTimeValueSeconds < 8 * 60 * 60) ? 'false' : 'true';
                    }

                    rowElement.dataset.synced = 'false';

                    const pendingLogRows = tableElement.querySelectorAll('.log:not([data-index])');
                    if (pendingLogRows.length === 0) {
                        APP.addLog(tableElement);
                    }
                }

                rowElement.classList.remove('loading-db');
                if (isTimeSpent) {
                    tableElement.classList.remove('loading-db');
                }
            });
        }
    },

    InputIssueKey: {
        regExp: /(\/browse\/)?(\w+-\d+)\/?.*/g,

        /**
         * @param {Event} event
         */
        onpaste: function (event) {
            event.preventDefault();

            const element = event.currentTarget,
                  clipboardText = (event.clipboardData || window.clipboardData).getData('text'),
                  issueKeyArray = [...clipboardText.matchAll(this.regExp)];

            if (issueKeyArray[0] && issueKeyArray[0][2]) {
                element.value = issueKeyArray[0][2];
            }
        }
    },

    InputTimeSpent: {
        regExp: /^([0-9]):([0-5][0-9]?)?$|^:([0-5][0-9]?)$|^([0-9])$/,

        specialKeys: [' ', ':', '.'],

        /**
         * @param {Event} event
         */
        onpaste: function (event) {
            const clipboardText = (event.clipboardData || window.clipboardData).getData('text');

            if (!this.regExp.test(clipboardText)) {
                event.preventDefault();
            }
        },

        /**
         * @param {HTMLElement} element
         */
        onchange: function (element) {
            const seconds = element.value.toSeconds();

            if (seconds >= 60) {
                element.value = seconds.fromSecondsToHoursMinutesFormat();
            } else {
                element.value = '';
            }
        },
 
        /**
         * With respect to the following comments, we consider the input value to be the form of "x:yz".
         *
         * @param {Event} event
         */
        oninput: function (event) {
            const element = event.currentTarget,
                  isBackspaceKey = event.inputType === 'deleteContentBackward',
                  isSpecialKey = this.specialKeys.includes(event.data);

            if (!element.value.includes(':') && isSpecialKey) {
                // Insert "0:" if the colon is not present in the value and the pressed key is special.
                element.value = '0:';
            }

            if (!element.value.includes(':') && !isBackspaceKey) {
                // Insert ":" if the colon is not present in the value and any key other than DELETE was pressed.
                element.value += ':';
            }

            if (isBackspaceKey) {
                // If the DELETE key was pressed...
                if (element.selectionStart === 1) {
                    // and if the cursor is located immediately after the "x" digit...
                    if (element.value.length === 1) {
                        // and if we don't have the "yz" digits... then set the value to an empty string,
                        element.value = '';
                    } else {
                        // otherwise, set the value to "0:yz".
                        element.value = '0:' + element.value.substring(element.selectionStart);
                    }
                }
            }
        },

        /**
         * @param {Event} event
         */
        onkeydown: function (event) {
            if (event.repeat) {
                event.preventDefault();
                return;
            }

            const element = event.currentTarget;

            if (event.metaKey || event.ctrlKey) {
                if (event.key.toUpperCase() === 'Z') {
                    element.value = element.dataset.value || '';
                    event.preventDefault();
                    return;
                }
            } else {
                const isSpecialKey = this.specialKeys.includes(event.key),
                      isDigit = (event.key >= '0') && (event.key <= '9'),
                      isNavKey = ['Meta', 'Control', 'Backspace', 'Delete', 'Shift', 'ArrowLeft', 'ArrowRight', 'Tab'].includes(event.key),
                      isNotValid1 = (parseInt(event.key) > 5) && (element.selectionStart === 2 || element.value.includes(':') && element.selectionStart === 1),
                      isNotValid2 = element.value.indexOf(':') === 0 && element.value.length === 3;

                if (isSpecialKey && element.value.includes(':') && element.value.length < 4) {
                    event.preventDefault();
                } else if (!isNavKey && (!isDigit && !isSpecialKey || isNotValid1 || isNotValid2)) {
                    event.preventDefault();
                }
            }
        }
    },

    ButtonJira: {
        /**
         * @param {Event} event
         */
        onclick: function (event) {
            const rowElement = event.currentTarget.closest('tr');

            rowElement.classList.remove('error');

            const id = rowElement.dataset.id,
                  index = rowElement.dataset.index,
                  started = rowElement.dataset.started;

            if (index && started) {
                let issueKeyElement = rowElement.querySelector('[data-name="issue_key"]'),
                    descriptionElement = rowElement.querySelector('[data-name="description"]'),
                    timeSpentElement = rowElement.querySelector('[data-name="time_spent"]');

                if (issueKeyElement && descriptionElement && timeSpentElement) {
                    let issueKey = issueKeyElement.value,
                        description = descriptionElement.value,
                        timeSpentSeconds = timeSpentElement.value.toSeconds() || 0;

                    if (issueKey && description && timeSpentSeconds) {
                        rowElement.classList.add('loading-jira');

                        APP.fetch(APP.JIRA.saveUrl, {
                            'id': id,
                            'issue_key': issueKey,
                            'description': description,
                            'time_spent_seconds': timeSpentSeconds,
                            'started': started
                        })
                        .then((response) => {
                            rowElement.classList.remove('loading-jira');

                            if (response.id) {
                                event.target.dataset.title = 'Update';
                                rowElement.dataset.id = response.id;
                                issueKeyElement.dataset.pointer = 'false';
                                issueKeyElement.setAttribute('readonly', '');

                                rowElement.classList.add('loading-db');
                                APP.fetch(APP.DB.saveUrl, {
                                    'index': index,
                                    'id': response.id,
                                    'synced': '1'
                                })
                                .then((response) => {
                                    rowElement.dataset.synced = 'true';
                                    rowElement.classList.remove('loading-db');
                                });
                            }
                        })
                        .catch((response) => {
                            console.log('Error! Response:');
                            console.log(response);
                            rowElement.classList.add('error');
                        });
                    } else {
                        console.log('Error: empty value');
                    }
                }
            }
        }
    },

    ButtonDb: {
        /**
         * @param {Event} event
         */
        onclick: function (event) {
            if (confirm(APP.translate('Are you sure you want to delete this record?'))) {
                const rowElement = event.currentTarget.closest('tr');

                rowElement.classList.remove('error');

                const index = rowElement.dataset.index;

                if (index) {
                    rowElement.classList.add('loading-db');
                    APP.fetch(APP.DB.deleteUrl, {
                        'index': index
                    })
                    .then((response) => {
                        rowElement.classList.remove('loading-db');
                        rowElement.remove();
                    });
                }
            }
        }
    },

    /**
     * @param {String} url
     * @param {Object} data
     */
    fetch: (url, data) => {
        const formData = new FormData();

        for (let key in data) {
            if (typeof data[key] !== 'undefined') {
                formData.append(key, encodeURIComponent(data[key]));
            }
        }

        return fetch(url, {
            method: 'POST',
            body: formData
        })
        .then((response) => {
            if (response.ok) return response.json();
            return Promise.reject(response);
        });
    }
};

document.addEventListener('DOMContentLoaded', APP.init.bind(APP));
