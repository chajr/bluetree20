<?php
/**
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  language
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.3.0
 */

/**
 * sample core translation array
 * @var array
 */
$content = array(
    'title'                         => 'jakiś tytuł strony',
    'error_title'                   => 'jakiś błąd',
    'warning_title'                 => 'tytuł ostrzeżenia',
    'info_title'                    => 'jakieś info',
    'ok_title'                      => 'jakiś ok',
    'error_other'                   => 'pozostałe informacje',
    'modul'                         => 'moduł',
    'require'                       => 'wymaga',
    'testowe_tulmaczenie_core'      => 'testowe tyłmaczenie',
    'tulmaczenie_przykladowe'       => 'Jakieś przykładowe tułmaczenie',
    'line'                          => 'linia',
    'file'                          => 'plik',
    'tekst_do_tulmaczenia'          => 'jakieś tułmaczenie',
    'external_legend'               => 'zewnętrzny html z layoutów głównych',
    'replace_paths'                 => 'zastępowanie ścierzek',
    'internal_link'                 => 'link wewnętrzny',
    'image'                         => 'jakiś obrazek',
    'system_markers'                => 'wanże znaczniki systemowe',
    'path'                          => 'ścieżka',
    'full'                          => 'pełna ścieżka',
    'domain'                        => 'domena',
    'lang'                          => 'język',
    'main_path'                     => 'bazowa ścieżka',
    'translations'                  => 'tułmaczenia',
    'framework_translations'        => 'tłumaczenia z frameworka',
    'module_translations'           => 'tłumaczenia z modułu',
    'module_code_translations'      => 'tłumaczenia dla podanego przez moduł kodu językowego',
    'framework_code_translations'   => 'tłumaczenia dla podanego przez framework kodu językowego',
    'blocks'                        => 'pozostałe bloki',
    'test_block'                    => 'blok testowy (moduły ładowane do bloku)',
    'error'                         => 'BŁĄD',
    'warning'                       => 'OSTRZEZNIE',
    'info'                          => 'INFORMACJA',
    'url_params'                    => 'generuje URL wraz z parametrami (zewnętrzna templatka)',
    'marker_replace'                => 'zastępowanie znacznika z module1',
    'page'                          => 'strona',
    'sub_page'                      => 'podstrona',
    'js_log'                        => 'js log dla frameworka',
    'string_to_translate'           => 'tekst z corowego pliku tułmaczeń',

    'example_description'           => 'Ta strona posiada linki do stron z pogrupowanymi przykładami użytkowania frameworka',
    'example_layouts'               => 'Przykład uzywania layoutów i templatek',
    'example_core_templates'        => 'Istotne templatki systemowe',
    'example_core_description'      => 'Przykłądy struktury i używania ważnych wbudowanych templatek systemowych',
    'example_layouts_usage'         => 'Przykłąd używania głównych templatek, templatek dla modułów, wczytywanie templatek do markerów i bloków i tworzenia layoutów przy użyciu markerów.',
    'example_title'                 => 'Przykłady Blue Frameworka',
    'example_generate'              => 'Tworzenie treści w templatkach',
    'example_generate_usage'        => 'Przykłady używania markerów do generowania treści (pętle, opcjonalne itp.) w templatkach.',
    'example_translate'             => 'Używanie tułmaczeń',
    'example_layout'                => 'Używanie templatek (w tym tworzenie instancji display_class wewnątrz modułu)',
    'example_sys_marks'             => 'Znaczniki Core',
    'example_blocks'                => 'Używanie bloków',
    'example_messages'              => 'Wiadomości o błędach, ostrzeżeniach, informacjach i powodzeniach',
    'example_incoming_data'         => 'Dane z GET, POST, SESSION, COOKIE, FILES',
    'example_incoming_data_usage'   => 'Jak używać dane z tablic globalnych oraz jak ustawiać dane w sesji i plikach cookie',
    'example_libraries'             => 'Używanie niektórych wbudowanych bibliotek',
    'example_libraries_description' => 'Przykład wczytywania, normalnego i statycznego używania bibliotek (zawiera biblioteki image i valid)',
    'example_modules'               => 'Komunikacja między modułami',
    'example_modules_description'   => 'Jak moduły mogą się ze sobą komunikować, wywoływać metody oraz kożystaćz własnej konfiguracji',
    'example_navigation'            => 'Breadcrumbs, mapa strony',
    'example_navigation_description'=> 'Pokazuje breadcrumbs oraz mapę strony jako tablice',
    'example_ajax'                  => 'Żądanie ajax',
    'example_inheritance'           => 'Dziedziczenie dla podstron',
    'example_exceptions'            => 'Wyjątki Frameworka',
    'test'                          => 'Test, sprawdza czy wszystkie elementy działają poprawnie',
    'main_page'                     => 'Strona główna',
    'generate_content'              => 'Tworzenie treści',
    'load_to_block'                 => 'Wczytywanie modułów do bloków',
    'load_to_block_usage'           => 'Przykłąd jak wczytać templatki z modułów do jednego znacznik bloku w kolejności uruchamiania',
    'page_not_found'                => 'Strona nie została znaleziona',
    'example_messages_usage'        => 'Przykłady tworzenia wiadomości (błędy ostrzezenia, itp.) to głównych znaczników systemowych lub wskazanych znaczników',
    'example_translate_usage'       => 'Przykłądy używania znaczników tułmaczeń w templatkach (uwzględniając tułmaczenia z innych plików oraz wymuszanie tułmaczeń)',
    'example_sys_marks_usage'       => 'Pokazuje znaczniki zamieniane przez treść z systemowych bibliotek',

    'simple_generate'                   => 'Zastępowanie zwykłego znacznika',
    'simple_generate_core'              => 'Zastępowanie zwykłego znacznika w głównej templatce',
    'optionally_marker_replaced'        => 'Opcjonalny znacznik - pokazany',
    'optionally_marker_none'            => 'Opcjonalny znacznik - usunięty',
    'loop_content'                      => 'Generowanie treści w pętli',
    'loop_content_empty'                => 'Generowanie treści w petli przy braku danych',
    'loop_content_missing_element'      => 'Generowanie treści w pętli z brakującymi elementami',
    'loop_content_nested'               => 'Generowanie treści dla zagnieżdżonych pętli',
    'loop_content_optional'             => 'Generowanie treści w pętli z opcjonalnymi znacznikami',
    'session_content'                   => 'Generowanie treści zapisanej w sesji',
    'content_array'                     => 'Generowanie treści z tablicy',
    'content_to_replace'                => 'Zastąpiona teść z modułu',
    'generate_content_core_template'    => 'Generowanie treści do znaczników w głównej templatce',
    'show_source'                       => 'Pokaż źródło',

    'framework_requirements'        => 'Wymagania',
    'framework_installation'        => 'Instalacja',
    'framework_examples'            => 'Przykłądy',
    'framework_documentation'       => 'Dokumentacja',
    'header_modules'                => 'Moduły',
    'header_libraries'              => 'Biblioteki PHP',
    'header_login'                  => 'Logowanie',
    'header_login_btn'              => 'Zaloguj',
    'current_language'              => 'Polski',
    'english'                       => 'Angielski',
    'polish'                        => 'Polski',
    'language_image'                => 'Poland.png',
    'back_to_top'                   => 'Do góry',
    'commit_gen'                    => 'Generator commitów',
    'requirements_title'            => 'Wymagania frameworka',
    'system_requirements'           => 'System oparty o Linuxa',
    'extension_enabled'             => 'włączone',
    'apache_enabled'                => 'Serwer Apache z włączonym mode_rewrite (jeśli opcja rewrite ustawiona na 1)',
    'htaccess_enabled'              => 'Możliwość modyfikacji plików .htaccess (jeśli opcja rewrite ustawiona na 1)',
    'base_requirements'             => 'Podstawowe wymagania',
    'optional_requirements'         => 'Wymagania zależne od wybranych opcji',
    'installation'                  => 'Instalacja frameworka',
    'installation_step_1'           => 'Ściągnij aktualną wersję frameworka z',
    'installation_step_2'           => 'Skopij wszystkie pliki frameworka na serwer',
    'installation_step_3'           => 'Zaktualizuj podstawową konfigurację (opisana wewnątrz dokumentacji frameworka)',
    'installation_step_4'           => 'I to wszystko, framework  jest gotowy do użycia',
);
