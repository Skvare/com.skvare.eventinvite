CRM.$(function($) {
    $('div.amount-item-section div.content').prepend('<div style="font-weight: bold;">RSVP: Not Attending</div>');
    var continueTxt = $('div.continue_message-section p').html();
    $('div.continue_message-section p').html(continueTxt.replace('registration', 'confirmation'));
});