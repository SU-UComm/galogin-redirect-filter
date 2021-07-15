# Google Apps Login Redirect Filter

When someone attempts to visit a private page that requires a user to login, the
[Google Apps Login plugin](https://wordpress.org/plugins/google-apps-login/)
intercepts the request and assigns a page to redirect to after the login handshake.
This works perfectly fine in most instances. However, in a Pantheon server cluster,
the variable the GA Login plugin uses to identify the host, `$_REQUEST['state']`,
instead points to a specific webhead in the server cluster. This results in a
mal-formed url, e.g.
`?redirect_to=https://appserver-3b68c6bd-nginx-a6325ef8d3ed4f7ba61d39c86c58a47e:16301?page_id=123`.
Attempts to redirect to the specific webhead fail, and the user ends up in the
backend, rather than on the page they were attempting to access when the login
was requested. This plugin adds a filter that replaces the webhead with the correct host
in the redirect url, e.g.
`?redirect_to=https://example.com?page_id=123`.