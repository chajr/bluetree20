<?php
$content = array(
    'settings'                              => 'Settings in',
    'reg_exp_rewrite'                       => 'regular expression to check that url is ok when mode rewrite is enabled, default',
    'reg_exp_classic'                       => 'regular expression to check that url is ok when mode rewrite is disabled, default',
    'global_var_check'                      => 'regular expression to check all variable key names given in post,files etc, default',
    'max_get'                               => 'max number of URL parameters <i>0 -no limit</i>, include pages and subpages',
    'max_post'                              => 'max number of POST parameters <i>0 -no limit</i>',
    'get_len'                               => 'max length of GET parameter, in rewrite mode include name + comma + variable <i>0 -no limit</i>',
    'post_secure'                           => 'if different on 0 convert POST values on entities or quotes <i>0 -none, 1 -quotes, 2 -entities</i>',
    'file_max_size'                         => 'set one uploaded file max size in kb, limit mut be set up <i>0 - always throw error when upload file</i>',
    'files_max_size'                        => 'set all uploaded files max size in kb, limit mut be set up and bigger than file_max_size <i>0 - always throw error when upload files</i>',
    'files_max'                             => 'set max number of uploaded files',
    'cookielifetime'                        => 'cookie lifetime in seconds <i>3600 == 1h</i>',
    'incoming_data_settings'                => 'Incoming data settings',
    'get_access'                            => 'GET access',
    'post_access'                           => 'POST access',
    'session_access'                        => 'SESSION access',
    'cookie_access'                         => 'COOKIE access',
    'file_access'                           => 'FILES access',
    'variable'                              => 'variable',
    'not_support'                           => 'content from session will not support translations',
    'put_value'                             => 'put some value here',
    'sent'                                  => 'sent value',
    'public'                                => 'public',
    'user'                                  => 'user',
    'new_public'                            => 'new public',
    'new_user'                              => 'new user',
    'cookie'                                => 'cookie',
    'sent_file'                             => 'sent file',
    'path'                                  => 'path',
    'rewrite_get'                           => 'rewrite get',
    'lang_code'                             => 'language code',
    'current_page'                          => 'current page',
    'parent_page'                           => 'parent page',
    'master_page'                           => 'master page',
    'full_get'                              => 'full get',
    'type'                                  => 'type',
    'with_domain'                           => 'path with domain',
    'complete_path'                         => 'complete path',
    'get_description'                       => '<p>
    Inside of module:<br/>
    To use data from **GET** we must use <i>$this->get->variableKey</i>.
    That will return data for given value or NULL if variable don\'t exists.
</p>
<p>
    In URL (depends on mode rewrite enable/disable):<br/>
    key,value or &key=value
</p>',
    'post_description'                       => '<p>
    Inside of module:<br/>
    Access to post data we get using the same way as get data $this->post->variableKey.
</p>',
    'session_description'                   => 'Access to session variable is the same way that previous examples, but session has specific data storage, split to some other arrays. Data in session is keep in that arrays: public, core, user, display.'
);
