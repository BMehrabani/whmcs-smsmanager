# whmcs-smsmanager

Download link: https://github.com/Jisort/whmcs-smsmanager/archive/master.zip

Installation instructions:

1. Extract the contents of the jisortwhmcs zip file in /modules/addons directory
2. Extract the contents of the jisorttwofactor zip file in /modules/security directory
3. In the addon modules page, you will see Jisort click activate then click configure to see the options
4. Go to Addons ->  Jisort, on the navigation bar at the top of WHMCS
5. Click 'Config' in the modules navigation bar
6. Enter your API Credentials which are used for sending SMS through your SMS gateway. (This is provided
by your SMS Gateway)
7. If you like to use custom client field, you can find in the dropdown box and change custom field used by
SMS manager, When you try to fresh install (new one) then module will add new custom field on client
custom field named "Mobile number", with this custom field we can get client mobile number to send
SMS, you can change to other fields.
8. Click the 'Save Changes' Button
9. Click 'Management' on the modules navigation bar
10. Turn on all of the notifications/alerts which you would like the module to automatically send to your
clients, and change the text of the SMS by modifying the textbox content.
11. Click the 'Save Changes' Button
12. If you want to enable Two-factor Authentication then please go to Setup -> Staff Management -> TwoFactor
Authentication on the navigation bar at the top of WHMCS
13. Scroll down to ' Jisort  - Two Factor Auth' and click the 'Activate' button
14. Tick the 'Enable for clients' if you want clients to be able to use this
 Tick the 'Enable for staff' if you want staff to be able to use this (YOU MUST PUT ALL admins
mobile numbers in the Two-Factor settings inside the SMS Manager module) and enable Force
Administrator Users to enable Two Factor Authentication.
15. Click the 'Save Changes' Button
16. Go back to Addons -> SMS Manager, on the navigation bar at the top of WHMCS
17. Click 'Admin Notifications' on the modules navigation bar
18. Next to each staff member, please enter their mobile number in international format but not including
any spaces or symbols
19. Click the 'Save Changes' Button
