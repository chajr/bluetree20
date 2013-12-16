<?php
/**
 * 
 *
 * @category    BlueFramework
 * @package     Blue
 * @subpackage  Object
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.1.0
 */
class blue_array_class
    extends blue_object_class
{
    public function arrayMerge()
    {
        $mergedArray = [];

        $arguments = func_get_args();
        foreach ($arguments as $arg) {
            if ($arg instanceof blue_object_class) {
                $arg = $arg->getData();
            }

            if (!is_array($arg)) {
                $arg = [$arg];
            }

            $mergedArray = array_merge($mergedArray, $arg);
        }
        
        //laczenie z data aktualnym

        return $this;
    }

    public function array_merge()
    {
        
    }
}
//array_change_key_case — Zwraca tablicę ze wszystkimi kluczami tekstowymi zamienionymi na wyłącznie małe lub wyłącznie duże litery
//array_chunk — Podziel tablicę na kawałki
//array_combine — Tworzy tablicę używając wartości jednej tablicy jako kluczy a drugiej jako wartości
//array_count_values — Zlicza wszystkie wartości w tablicy
//array_diff_assoc — Oblicza różnicę między tablicami z dodatkowym sprawdzaniem kluczy
//array_diff_key — Oblicza różnicę tablic używając kluczy do porównań
//array_diff_uassoc — Computes the difference of arrays with additional index check which is performed by a user supplied callback function
//array_diff_ukey — Oblicza różnicę tablic używając funkcji zwrotnej do porównywania kluczy
//array_diff — Zwraca różnice pomiędzy tablicami
//array_fill_keys — Fill an array with values, specifying keys
//array_fill — Wypełnij tablicę podanymi wartościami
//array_filter — Filtruje elementy przy użyciu funkcji zwrotnej
//array_flip — Wymienia wszystkie klucze z przypisanymi do nich wartościami w tablicy
//array_intersect_assoc — Wylicza przecięcie tablic z dodatkowym sprawdzaniem indeksów
//array_intersect_key — Computes the intersection of arrays using keys for comparison
//                                                                         array_intersect_uassoc — Computes the intersection of arrays with additional index check, compares indexes by a callback function
//array_intersect_ukey — Computes the intersection of arrays using a callback function on the keys for comparison
//                                                                                                     array_intersect — Zwraca przecięcie tablic
//array_key_exists — Sprawdza czy podany klucz lub indeks istnieje w tablicy
//array_keys — Zwraca wszystkie klucze z tablicy
//array_map — Wykonuje funkcję zwrotną na elementach podanej tablicy
//array_merge_recursive — Łączy dwie lub więcej tablic rekurencyjnie
//array_merge — Łączy jedną lub więcej tablic
//array_multisort — Sortuje wiele tablic lub wielowymiarowe tablice
//array_pad — Dopełnij tablicę do podanej długości podanymi wartościami
//array_pop — Zdejmij element z końca tablicy
//array_product — Calculate the product of values in an array
//    array_push — Wstaw jeden lub więcej elementów na koniec tablicy
//array_rand — Wybierz jeden lub więcej losowych elementów z tablicy
//array_reduce — Iteracyjnie zredukuj tablicę do pojedyńczej wartości używając funkcji zwrotnej
//array_replace_recursive — Replaces elements from passed arrays into the first array recursively
//array_replace — Replaces elements from passed arrays into the first array
//    array_reverse — Zwraca tablicę z elementami ustawionymi w porządku odwrotnym
//array_search — Przeszukuje tablicę pod kątem podanej wartości i w przypadku sukcesu zwraca odpowiedni klucz
//array_shift — Usuń element z początku tablicy
//array_slice — Wytnij kawałek tablicy
//array_splice — Usuń część tablicy i zamień ją na coś innego
//array_sum — Oblicza sumę wartości w tablicy
//array_udiff_assoc — Computes the difference of arrays with additional index check, compares data by a callback function
//array_udiff_uassoc — Computes the difference of arrays with additional index check, compares data and indexes by a callback function
//array_udiff — Computes the difference of arrays by using a callback function for data comparison
//                                                                                 array_uintersect_assoc — Oblicza przecięcie tablic z dodatkowym sprawdzaniem indeksów, porównując dane przez funkcję zwrotną
//array_uintersect_uassoc — Computes the intersection of arrays with additional index check, compares data and indexes by a callback functions
//array_uintersect — Computes the intersection of arrays, compares data by a callback function
//array_unique — Usuwa duplikaty wartości z tablicy
//array_unshift — Wstaw jeden lub więcej elementów na początek tablicy
//array_values — Zwraca wszystkie wartości z tablicy
//array_walk_recursive — Apply a user function recursively to every member of an array
//    array_walk — Zastosuj funkcję użytkownika do każdego elementu tablicy
//array — Stwórz tablicę
//arsort — Sortuje tablicę w porządku odwrotnym z zachowaniem skojarzenia kluczy
//asort — Posortuj tablicę zachowując skojarzenia kluczy
//compact — Stwórz tablicę zawierającą zmienne i ich wartości
//count — Zlicza ilość elementów w tablicy lub pól obiektu
//current — Zwraca bieżący element tablicy
//each — Zwraca bieżącą parę klucza i wartości z tablicy i przesuwa kursor tablicy
//end — Ustawia wewnętrzny wskaźnik tablicy na ostatnim elemencie
//extract — Importuj zmienne do bieżącej tablicy symboli z tablicy
//in_array — Sprawdza czy wartość istnieje w tablicy
//key — Pobiera klucz z tablicy asocjacyjnej
//krsort — Sortuj tablicę według kluczy w porządku odwrotnym
//ksort — Sortuj tablicę według klucza
//list — Przypisz zmienne tak jakby były tablicą
//natcasesort — Sortuj tablicę używając algorytmu "porządek naturalny" ignorującego wielkość znaków
//natsort — Sortuj tablicę używając algortmu "porządek naturalny"
//next — Przesuń do przodu wewnętrzny wskaźnik tablicy
//pos — Alias dla current
//prev — Cofnij wewnętrzny wskaźnik tablicy
//range — Stwórz tablicę zawierającą przedział elementów
//reset — Ustaw wewnętrzny wskaźnik tablicy na jej pierwszy element
//rsort — Sortuj tablicę w porządku odwrotnym
//shuffle — Przetasuj tablicę
//sizeof — Alias dla count
//sort — Sortuje tablicę
//uasort — Sortuj tablicę korzystając ze zdefiniowanej przez użytkownika funkcji porównującej i zachowując skojarzenia kluczy
//uksort — Sortuj tablicę według kluczy korzystając ze zdefiniowanej przez użytkownika funkcji porównującej
//usort — Sortuje tablicę według wartości korzystając ze zdefiniowanej przez użytkownika funkcji porównującej

//array_multisort()	value	associative yes, numeric no	first array or sort options	array_walk()
//asort()	value	yes	low to high	arsort()
//arsort()	value	yes	high to low	asort()
//krsort()	key	yes	high to low	ksort()
//ksort()	key	yes	low to high	asort()
//natcasesort()	value	yes	natural, case insensitive	natsort()
//natsort()	value	yes	natural	natcasesort()
//rsort()	value	no	high to low	sort()
//shuffle()	value	no	random	array_rand()
//sort()	value	no	low to high	rsort()
//uasort()	value	yes	user defined	uksort()
//uksort()	key	yes	user defined	uasort()
//usort()	value	no	user defined	uasort()