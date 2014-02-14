<?php
$content = array(
    'settings'                              => 'Ustawienia w',
    'reg_exp_rewrite'                       => 'wyrażenie regularne aby sprawdzić czy url pasuje jeśli mode rewrite jest włączony, domyślnie',
    'reg_exp_classic'                       => 'wyrażenie regularne aby sprawdzić czy url pasuje jeśli mode rewrite jest wyłączony, domyślnie',
    'global_var_check'                      => 'wyrażenie regularne aby sprawdzić nazwy klucza podane w post, files itp, domyślnie',
    'max_get'                               => 'maksymalna liczba parametrów w URL <i>0 - bez limitu</i>, łącznie ze stronami i podstronami',
    'max_post'                              => 'maksymalna liczba parametrów POST <i>0 - bez limitu</i>',
    'get_len'                               => 'maksymalna długość parametrów max length of GET parameter, in rewrite mode include name + comma + variable <i>0 -no limit</i>',
    'post_secure'                           => 'jeśli rózne od 0 zamienia wartości POST na encje lub cytaty <i>0 -brak, 1 -cytaty, 2 -encje</i>',
    'file_max_size'                         => 'ustawia maksymalną wielkość pojedyńczego wysłąnego pliku w kb, limit musi być ustawiony <i>0 - zawsze zwróci wyjątek jeśli będzie przesłany plik</i>',
    'files_max_size'                        => 'ustawia maksymalną wielkość wszystkich wysłanych plików w kb, limit musi być ustawiony i większy od file_max_size <i>0 - zawsze zwróci wyjątek jeśli będzie przesłany plik</i>',
    'files_max'                             => 'ustawia maksymalną liczbę przesłanych plików',
    'cookielifetime'                        => 'czas ycia plików cookie w sekundach <i>3600 == 1h</i>',
    'incoming_data_settings'                => 'Ustawienia dla danych przychodzących',
    'get_access'                            => 'Dostęp do GET',
    'post_access'                           => 'Dostęp do POST',
    'session_access'                        => 'Dostęp do SESSION',
    'cookie_access'                         => 'Dostęp do COOKIE',
    'file_access'                           => 'Dostęp do FILES',
    'variable'                              => 'zmienna',
    'not_support'                           => 'treść z sesji nie obsługuje tułmaczń',
    'put_value'                             => 'wpisz jakieś dane',
    'sent'                                  => 'wyślij dane',
    'public'                                => 'publiczny',
    'user'                                  => 'użytkownik',
    'new_public'                            => 'nowy publiczny',
    'new_user'                              => 'nowy użytkownik',
    'cookie'                                => 'ciasteczka',
    'sent_file'                             => 'wyślij plik',
    'get_description'                       => '<p>
    Wewnątrz modułu:<br/>
    Aby używać danych z **GET** musimy użyć <i>$this->get->variableKey</i>.
    To zwróci dane dla podanej wartości lub NULL jeśli zmienna nie istnieje.
</p>
<p>
    W URL (zależnie czy mode rewrite jes włączony/wyłączony):<br/>
    klucz,wartość lub &klucz=wartość
</p>',
    'post_description'                      => '<p>
    Wewnątrz modułu:<br/>
    Dostęp do danych post uzyskujemy w ten sam sposób co get $this->post->variableKey.
</p>',
    'session_description'                   => 'Dostęp do zmiennych w sesji uzyskujemy tak samo jak w poprzednich przykładach, lecz sesja posiada specyficzny sposób przechowywania danych, podzielony na tablice. Dane w sesji trzymane są w następujących tablicach: public, core, user, display.'
);
