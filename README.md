Table of Contents
=================
* [Περιγραφή API](#περιγραφή-api)
    * [Methods](#api-methods)
        * [Board](#api-board)
            * [Εμφάνηση Board](#api-showboard)
            * [Εμφάνηση Board](#api-resetboard)
            * [Κίνιση](#api-makemove)
        * [Player](#api-player)
            * [Login](#api-login)
            * [Logout](#api-logout)
* [Login](#classes)
    * [Board](#classes-board)
        * [Fields](#board-fields)
        * [Methods](#board-methods)
    * [Player](#classes-player)
        * [Fileds](#player-fields)
        * [Methods](#player-methods)
    

<a name="περιγραφή-api"></a>
# Περιγραφή API

<a name="api-methods"></a>
## Methods

<a name="api-board"></a>
### Board

<a name="api-showboard"></a>
#### Ανάγνωση Board

```
GET /showboard/
```

Επιστρέφει το Board

<a name="api-resetboard"></a>
#### Αρχικοποίηση Board

```
POST /resetboard/
```

Αδιάζει το Board από τα πούλια. 

<a name="api-makemove"></a>
#### Κίνηση 

```
POST /makemove/:username/:x
```
Json Data:

| Field       | Description                    | Required |
| ----------- | ------------------------------ | -------- |
| 'username'  | Το username του παίκτη         | yes      |
| 'x'         | Η στήλη που ρίχνει το πιόνι    | yes      |

Εκτελεί την κινηση του παίκτη. Δηλαδή τοποθετεί στην στήλη x που επιλέχθηκε από τον παίκτη το πιόνι του χρώματός του.
Ελέγχεται η εγκυρότητα της κίνησης καθώς υπάρχει πιθανότητα η στήλη που επιλέχθηκε να είναι γεμάτη. Επίσης ελέγχεται
αν η κίνηση είναι και νικητήρια.

<a name="api-player"></a>
### Player

<a name="api-login"></a>
#### Login

```
POST /login/:username/:password
```

| Field       | Description                    | Required |
| ----------- | ------------------------------ | -------- |
| 'username'  | Το username του παίκτη         | yes      |
| 'password'  | To password του παίκτη         | yes      |

Ελέγχει στην βάση τα στοιχεία που έδωσε το παίκτης ώστε, αν είναι σωστά και δεν είναι
ήδη συνδεδεμένος να τον συνδέσει και να του εκχωρίσει το χρώμα πιονιού που θα έχει στο παιχνίδι.

<a name="api-logout"></a>
#### Logout

```
GET /logout/:username/
```

| Field       | Description                    | Required |
| ----------- | ------------------------------ | -------- |
| 'username'  | Το username του παίκτη         | yes      |

Ελέγχει αν είναι ήδη συνδεδεμένος ο παίκτης και τον αποσυνδέει.

<a name="classes"></a>
# Classes

<a name="classes-board"></a>
## Board

<a name="board-fields"></a>
### Fields

Το Board αποτελείται από τα παρακάτω πεδία:

| Field              | Description                                                        | Values   |
| ------------------ | ------------------------------------------------------------------ | -------- |
| 'board'            | Πίνακας που αποθηκεύονται τα πιόνια στις κατάλληλες θέσεις         | 'R','Y'  |
| 'topOfEachColumn'  | Πίνακας με την υψηλότερη θέση σε κάθε στήλη που περιέχει πιόνι     | 0..6     |

<a name="board-methods"></a>
### Methods

| Method             |  Parameters | Description                                                                    |
| ------------------ | ----------- | ------------------------------------------------------------------------------ |
| 'constructor'      |             | Αρχικοποίηση των πεδίων στην βάση                                              |
| 'show_board'       |  -          | Εμφανίζει το board την δεδομένη στιγμή                                         |
| 'updateTop'        |  -          | Κάνει update στην βάση για την υψηλότερη θέση στην στήλη που έγινε η κίνηση    |
| 'move'             |  x          | Εκχωρεί στην κατάλληλη θέση(x) του πίνακα board χρώμα του πιονιού που παίχτηκε |
| 'isWinningMove'    |  x,color    | Καλεί μεθόδους που ελέγχουν αν η τελευταία κίνηση είναι και η νικητήρια color = χρώμα κίνηση, x = στήλη κίνησης      |
| 'horizontal'       |  χ,color    | Ελέγχει αν η τελευταία κίνηση κέρδισε οριζόντια. Δηλαδή αν υπάρχουν οριζόντια 4 πιόνια ίδιου χρώματος. color = χρώμα κίνηση, x = στήλη κίνησης |
| 'vertical'         |  x,color    | Ελέγχει αν η τελευταία κίνηση κέρδισε κάθετα. Δηλαδή αν υπάρχουν κάθετα 4 πιόνια ίδιου χρώματος.  color = χρώμα κίνηση, x = στήλη κίνησης      |
| 'backDia'          |  x,color    | Ελέγχει αν η τελευταία κίνηση κέρδισε πίσω διαγώνια. Δηλαδή αν υπάρχουν διαγώνια, από χαμηλό ύψος σε υψηλό ύψος, 4 πιόνια ίδιου χρώματος.  color = χρώμα κίνηση, x = στήλη κίνησης      |
| 'frontDia'         |  x,color    | Ελέγχει αν η τελευταία κίνηση κέρδισε μπροστά διαγώνια. Δηλαδή αν υπάρχουν διαγώνια, από υψηλό ύψος σε χαμηλό ύψος, 4 πιόνια ίδιου χρώματος.  color = χρώμα κίνηση, x = στήλη κίνησης      |
| 'streakFlag'       |  streak     | Πέρνει το streak από της τις μεθόδους ελέγχου νίκης (horizontal,vertical,backDia,frontDia) ώστε αν είναι πάνω από 4 να επιστρέψει true, δηλαδή νικητήρια κίνηση. streak = συνεχόμενα πιόνια ίδιου χρώματος   |
| 'checkTopOfX'      |  x          | Ελέγχει αν το υψηλότερο πιόνι στην θέση x είναι σε ύψος 6 ώστε αν είναι στην 6 να μην επιτραπεί η κίνηση      |
| 'getBoard'         |  -          | Επιστρέφει το board σε json μορφή                                              |
| 'boardFillFromDB'  |  -          | Γεμίζει ο πίνακας board με τα δεδομένα της βάσης                               |

<a name="classes-player"></a>
## Player


<a name="player-fields"></a>
### Fields

| Field              | Description                                                        | Values   |
| ------------------ | ------------------------------------------------------------------ | -------- |
| 'username'         | Ένα string με το username του παίκτη                               | String   |
| 'status'           | Κατάσταση σύνδεσης του παίκτη. 0 = offline, 1 = online             | 0,1      |
| 'color'            | Το χρώμα πιονιού του παίκτη που εκχωρείται από το σύστημα          | 'R','Y'  |


<a name="player-methods"></a>
### Methods 

| Method             |  Parameters        | Description                                                                    |
| ------------------ | ------------------ | ------------------------------------------------------------------------------ |
| 'constructor'      | username, password | Καλεί την μέθοδο login ώστε να συνδεθεί ο χρήσης                                              |
| 'getColor'         | -                  | Επιστρέφει το color του παίκτη
| 'getUsername'      | -                  | Επιστρέφει το username του παίκτη              |
| 'login'            | username, password | Ελέγχει στην βάση αν ο παίκτης είναι ήδη συνδεδεμένος και αν όχι, ελέγχει αν είναι έγκυρα το username και το password ώστε να αλλάξει το status του παίκτη σε 1 και να του δώσει χρώμα |
| 'logout'           | -                  | Ελέγχει αν ο παίκτης είναι συνδεδεμένος ώστε να του αλλάξει το status σε 0 και να του αφαιρέσει το χρώμα πιονιού              |
| 'checkStatus'      | -                  | Ελέγχει αν το παίκτης είναι συνδεδεμένος και επιστρέφει false ή true             |

