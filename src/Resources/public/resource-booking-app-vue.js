/**
 * Resource Booking Module for Contao CMS
 * Copyright (c) 2008-2019 Marko Cupic
 * @package resource-booking-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2019
 * @link https://github.com/markocupic/resource-booking-bundle
 */
"use strict";

var resourceBookingApp = new Vue({
    el: '#resourceBookingApp',
    data: {
        // idle time in milliseconds
        idleTimeLimit: 300000,
        userLoggedOut: false,
        isOnline: true,
        isReady: false,
        loggedInUser: [],
        resourceIsAvailable: true,
        requestToken: '',
        weekdays: [],
        activeWeek: {},
        timeSlots: [],
        rows: [],
        activeResource: {},
        activeResourceType: {},
        bookingRepeatsSelection: [],
        bookingFormValidation: [],
        bookingModal: {},
        form: {}
    },
    created: function created() {
        var self = this;
        self.requestToken = RESOURCE_BOOKING.requestToken; // Load data from server

        // Get data from server each 15s
        self.getDataAll();
        window.setInterval(function () {
            self.getDataAll();
        }, 15000);

        // Initialize idle detector
        self.initializeIdleDetector();

        // Check Online status each 60 s
        self.sendIsOnlineRequest();
        window.setInterval(function () {
            self.sendIsOnlineRequest();
        }, 60000);

        window.setTimeout(function () {
            self.isReady = true;
        }, 800);
    },
    watch: {
        // Watcher
        isOnline: function isOnline(val) {
            var self = this;
            if (val === false) {
                // Logout user after 5 min of idle time
                self.sendLogoutRequest();
                window.setTimeout(function () {
                    window.setTimeout(function () {
                        $('#autoLogoutModal').on('hidden.bs.modal', function () {
                            location.reload();
                        });
                        $('#autoLogoutModal').modal('show');
                    }, 100);
                }, 400);
            }
        }
    },
    methods: {
        /**
         * Get all the rows from server
         */
        getDataAll: function getDataAll() {
            var self = this;
            var xhr = $.ajax({
                url: window.location.href,
                type: 'post',
                dataType: 'json',
                data: {
                    'REQUEST_TOKEN': self.requestToken,
                    'action': 'getDataAll'
                }
            });
            xhr.done(function (response) {
                if (response.status === 'success') {
                    for (var key in response['data']) {
                        self[key] = response['data'][key];
                    }
                }
            });
            xhr.fail(function ($res, $bl) {
                self.isOnline = false;
            });
            xhr.always(function () {//
            });
        },

        /**
         * Open booking modal window
         * @param objActiveTimeSlot
         * @param action
         */
        openBookingModal: function openBookingModal(objActiveTimeSlot, action) {
            var self = this;
            self.bookingModal.selectedTimeSlots = [];
            self.bookingModal.action = action;
            self.bookingModal.showConfirmationMsg = false;
            self.bookingModal.activeTimeSlot = objActiveTimeSlot;
            self.bookingModal.alertSuccess = '';
            self.bookingModal.alertError = '';
            self.bookingModal.selectedTimeSlots.push(objActiveTimeSlot.bookingCheckboxValue);
            self.bookingFormValidation = [];
            window.setTimeout(function () {
                self.sendBookingFormValidationRequest();
            }, 500);
            $('#resourceBookingModal [name="bookingDescription"]').val('');
            $('#bookingRepeatStopWeekTstamp option').prop('selected', false);
            $('#bookingRepeatStopWeekTstamp [data-current-week="true"]').prop('selected', 'selected');
            $('#resourceBookingModal').modal('show');
        },

        /**
         * Send booking request
         */
        sendBookingRequest: function sendBookingRequest() {
            var self = this;
            var xhr = $.ajax({
                url: window.location.href,
                type: 'post',
                dataType: 'json',
                data: {
                    'action': 'sendBookingRequest',
                    'REQUEST_TOKEN': self.requestToken,
                    'resourceId': self.bookingModal.activeTimeSlot.resourceId,
                    'description': $('#resourceBookingModal [name="bookingDescription"]').val(),
                    'bookingDateSelection': self.bookingModal.selectedTimeSlots,
                    'bookingRepeatStopWeekTstamp': $('#bookingRepeatStopWeekTstamp').val()
                }
            });
            xhr.done(function (response) {
                if (response.status === 'success') {
                    self.bookingModal.alertSuccess = response.alertSuccess;
                    window.setTimeout(function () {
                        $('#resourceBookingModal').modal('hide');
                    }, 2500);
                } else {
                    self.bookingModal.alertError = response.alertError;
                }
            });
            xhr.fail(function () {
                alert("Verbindung zum Server fehlgeschlagen! Überprüfen Sie die Netzwerkverbindung bitte.");
            });
            xhr.always(function () {
                self.bookingModal.showConfirmationMsg = true;
                self.getDataAll();
            });
        },

        /**
         * Send resource availability request
         */
        sendBookingFormValidationRequest: function sendBookingFormValidationRequest() {
            var self = this;
            var xhr = $.ajax({
                url: window.location.href,
                type: 'post',
                dataType: 'json',
                data: {
                    'action': 'sendBookingFormValidationRequest',
                    'REQUEST_TOKEN': self.requestToken,
                    'resourceId': self.bookingModal.activeTimeSlot.resourceId,
                    'bookingDateSelection': self.bookingModal.selectedTimeSlots,
                    'bookingRepeatStopWeekTstamp': $('#bookingRepeatStopWeekTstamp').val()
                }
            });
            xhr.done(function (response) {
                if (response.status === 'success') {
                    self.bookingFormValidation = response.bookingFormValidation;
                }
            });
            xhr.fail(function () {
                self.isOnline = false;
            });
            xhr.always(function () {//
            });
        },

        /**
         * Send cancel booking request
         */
        sendCancelBookingRequest: function sendCancelBookingRequest() {
            var self = this;
            var xhr = $.ajax({
                url: window.location.href,
                type: 'post',
                dataType: 'json',
                data: {
                    'action': 'sendCancelBookingRequest',
                    'REQUEST_TOKEN': self.requestToken,
                    'bookingId': self.bookingModal.activeTimeSlot.bookingId
                }
            });
            xhr.done(function (response) {
                if (response.status === 'success') {
                    self.bookingModal.alertSuccess = response.alertSuccess;
                    window.setTimeout(function () {
                        $('#resourceBookingModal').modal('hide');
                    }, 2500);
                } else {
                    self.bookingModal.alertError = response.alertError;
                }
            });
            xhr.fail(function () {
                self.isOnline = false;
            });
            xhr.always(function () {
                self.bookingModal.showConfirmationMsg = true;
                self.getDataAll();
            });
        },

        /**
         * Send booking request
         */
        sendIsOnlineRequest: function sendIsOnlineRequest() {
            var self = this;
            var xhr = $.ajax({
                url: window.location.href,
                type: 'post',
                dataType: 'json',
                data: {
                    'action': 'sendIsOnlineRequest',
                    'REQUEST_TOKEN': self.requestToken,
                }
            });
            xhr.done(function (response) {
                if (response.status === 'success') {
                    self.isOnline = response.isOnline;
                } else {
                    self.isOnline = false;
                }
            });
            xhr.fail(function () {
                self.isOnline = false;
            });
            xhr.always(function () {
                //
            });
        },

        /**
         * Send logout request
         */
        sendLogoutRequest: function sendLogoutRequest() {
            var self = this;
            var xhr = $.ajax({
                url: window.location.href,
                type: 'post',
                dataType: 'json',
                data: {
                    'action': 'sendLogoutRequest',
                    'REQUEST_TOKEN': self.requestToken,
                }
            });
            xhr.always(function () {
                self.isOnline = false;
                self.userLoggedOut = true;
            });
        },

        /**
         * initialize idle detector
         */
        initializeIdleDetector: function initializeIdleDetector() {
            var self = this;
            $(document).idle({
                onIdle: function () {
                    self.sendLogoutRequest();
                },
                idle: self.idleTimeLimit
            });
        },

        /**
         * submit form on change
         */
        submitForm: function submitForm() {
            var self = this;
            document.getElementById('resourceBookingForm').submit();
        },


    }
});
