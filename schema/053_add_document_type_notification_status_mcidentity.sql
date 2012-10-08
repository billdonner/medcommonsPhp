--- Notification status tracks whether the user has been notified about
--- the document yet.  This is intended for asynchronous notifications 
--- where delivery of the notification needs confirmation from the user
--- eg. the Current CCR where modifications have to be highlighted.
alter table document_type add column dt_notification_status varchar(30);
