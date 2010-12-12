function fb_login(redirect, action)
{
    FB.login(function(response) 
    {
        if (response.session) 
        {
            if (response.perms) 
            {
                // user is logged in and granted some permissions.
                // perms is a comma separated list of granted permissions
                window.location = '/auth/facebook?r=' + ((redirect) ? redirect : '');
            } 
        } 
    }, {perms:'email, user_birthday'}); // email is required
}