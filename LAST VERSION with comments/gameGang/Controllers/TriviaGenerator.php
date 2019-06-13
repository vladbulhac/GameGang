<?php
require_once 'Database.php';

class Trivia
{
    public $chosen_trivia;

    public function __construct()
    {
        $this->dailyTrivia();
    }

    public function dailyTrivia()
    {
        $db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');
        $query = "SELECT id_trivia,generated_date FROM generated_trivia";
        $res = $db->conn->query($query);

        if ($res->num_rows > 0) { //a fost deja generat un trivia of the day, verific daca au trecut 24 de ore de atunci
            $today = date('Y-m-d H:i:s');
            $row = $res->fetch_assoc();
            $timediff = strtotime($today) - strtotime($row['generated_date']); //diferenta dintre ora de acum si ora la care a fost generat trivia
            $current_trivia = $row['id_trivia'];

            if ($timediff > 86400) { //diferenta mai mare de 24 de ore deci alegem alta intrebare
                $ok = 0;

                $query = "SELECT id FROM trivia";
                $res = $db->conn->query($query);
                $trivia_num = $res->num_rows;

                while ($ok == 0) {

                    $shuffle = rand(1, $trivia_num); //aleg un numar random intre 1 si numarul de intrebari din baza de date
                    while ($shuffle == $current_trivia) {$shuffle = rand(1, $trivia_num);} //cat timp intrebarea aleasa este cea din tabelul generated trivia, aleg alta

                    $stmt = $db->conn->prepare("SELECT id FROM trivia WHERE id=?");
                    $stmt->bind_param('i', $shuffle);
                    $stmt->execute();
                    $res = $stmt->get_result();

                    if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                        $ok = 1;
                        $this->chosen_trivia = $shuffle;
                        $query = "DELETE FROM generated_trivia WHERE id=1"; //sterg din tabelul generated trivia, intrebarea
                        if ($db->conn->query($query) == false) {
                            echo 'Error at generating new trivia for today';
                        }
                        $query = "INSERT INTO generated_trivia (id,id_trivia) VALUES (1,?)"; //inserez noua intrebare care o sa fie trivia of the day
                        $stmt = $db->conn->prepare($query);
                        $stmt->bind_param('i', $shuffle);
                        $stmt->execute();
                    }
                }
            } else {
                $this->chosen_trivia = $row['id_trivia']; //nu este diferenta mai mare de 24 de ore asa ca nu alegem alta intrebare
            }

        } else { $ok = 0; //tabelul generated trivia este gol, e prima data cand se genereaza un trivia of the day
            $query = "SELECT id FROM trivia";
            $res = $db->conn->query($query);
            $trivia_num = $res->num_rows;
            while ($ok == 0) {

                $shuffle = rand(1, $trivia_num); //se alege o intrebare random

                $stmt = $db->conn->prepare("SELECT id FROM trivia WHERE id=?");
                $stmt->bind_param('i', $shuffle);
                $stmt->execute();
                $res = $stmt->get_result();

                if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                    $ok = 1;
                    $this->chosen_trivia = $shuffle;
                    $query = "DELETE FROM generated_trivia WHERE id=1";
                    if ($db->conn->query($query) == false) {
                        echo 'Error at generating new trivia for today';
                    }
                    $query = "INSERT INTO generated_trivia (id,id_trivia) VALUES (1,?)"; //inserez noua intrebare care o sa fie trivia of the day
                    $stmt = $db->conn->prepare($query);
                    $stmt->bind_param('i', $shuffle);
                    $stmt->execute();
                }
            }
        }
    }

}
