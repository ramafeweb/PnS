<style>
  /* ===== Google Font Import - Poppins ===== */
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
  }

  body {
    background-image: url('img/texture.png');
    background-repeat: no-repeat;
    background-size: cover;
    backdrop-filter: blur(3px);

    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }


  h1 {
    text-align: center;
    margin: 2rem 0;
    font-size: 2rem;
  }

  h2 {
    font-size: 1.8rem;
    margin-top: 3rem;
  }

  .card {
    margin-bottom: 5rem;

    background-color: whitesmoke;
    width: 60%;
    padding: 3rem;
    border-radius: 10px;
    box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;
  }

  b {
    color: red;
  }

  h3 {
    font-size: 1.4rem
  }

  p {
    font-size: 1.3rem;
  }

  a {
    border: black solid 2px;
    margin: 2rem auto;
    padding: 1rem 3rem;
    display: block;
    text-align: center;
    text-decoration: none;
    font-size: 1.5rem;
    border-radius: 8px;
    background-color: blueviolet;
    color: white;
    font-weight: 500;
  }

  a:hover {
    background-color: rgb(182, 124, 236);
  }
</style>
<?php

use function PHPSTORM_META\type;

include 'koneksi.php';

if (isset($_POST['evidence'])) {
  echo "<h1>Hasil Diagnosa Penyakit</h1>";
  $evidenceCount = count($_POST['evidence']);
  if ($evidenceCount < 2) {
    echo "Pilih minimal 2 gejala";
  } else {
    $selectedEvidence = implode(',', $_POST['evidence']);
    $sql = "SELECT GROUP_CONCAT(b.code), a.cf
                FROM ds_rules a
                JOIN ds_problems b ON a.id_problem=b.id
                WHERE a.id_evidence IN(" . $selectedEvidence . ")
                GROUP BY a.id_evidence";


    print_r($_POST['evidence']);

    $result = $db->query($sql);
    $evidence = [];
    while ($row = $result->fetch_row()) {
      $evidence[] = $row;
    }

    // Menentukan environment
    $sql = "SELECT GROUP_CONCAT(code) FROM ds_problems";
    $result = $db->query($sql);
    $row = $result->fetch_row();
    $fod = $row[0];

    // Menentukan nilai densitas
    echo "<div class='card'>";
    $urutan = 1;
    $densitas_baru = [];
    while (!empty($evidence)) {
      $densitas1[0] = array_shift($evidence);
      $densitas1[1] = array($fod, 1 - $densitas1[0][1]);

      $densitas2 = [];
      if (empty($densitas_baru)) {
        $densitas2[0] = array_shift($evidence);
      } else {
        foreach ($densitas_baru as $k => $r) {
          if ($k != "&theta;") {
            $densitas2[] = array($k, $r);
          }
        }
      }

      $theta = 1;
      foreach ($densitas2 as $d) $theta -= $d[1];
      $densitas2[] = array($fod, $theta);
      $m = count($densitas2);
      $densitas_baru = [];

      for ($y = 0; $y < $m; $y++) {
        for ($x = 0; $x < 2; $x++) {
          if (!($y == $m - 1 && $x == 1)) {
            $v = explode(',', $densitas1[$x][0]);
            $w = explode(',', $densitas2[$y][0]);
            sort($v);
            sort($w);
            $vw = array_intersect($v, $w);
            if (empty($vw)) {
              $k = "&theta;";
            } else {
              $k = implode(',', $vw);
            }
            if (!isset($densitas_baru[$k])) {
              $densitas_baru[$k] = $densitas1[$x][1] * $densitas2[$y][1];
            } else {
              $densitas_baru[$k] += $densitas1[$x][1] * $densitas2[$y][1];
            }
          }
        }
      }

      foreach ($densitas_baru as $k => $d) {
        if ($k != "&theta;") {
          $densitas_baru[$k] = $d / (1 - (isset($densitas_baru["&theta;"]) ? $densitas_baru["&theta;"] : 0));
        }
      }
      echo "<h3>Langkah " . $urutan . "</h3> ";
      $urutan++;
      foreach ($densitas_baru as
        $key => $value) {
        echo "<p>$key = $value</p>";
      }
      echo "<hr>";
    }

    // Perangkingan
    unset($densitas_baru["&theta;"]);
    arsort($densitas_baru);
    echo "<h3>Perangkingan nilai</h3>";
    foreach ($densitas_baru as
      $key => $value) {
      echo "<p>$key = $value</p>";
    }
    echo "<hr>";

    // Menampilkan hasil akhir
    $codes = array_keys($densitas_baru);
    $sql = "SELECT GROUP_CONCAT(name) FROM ds_problems WHERE code IN('{$codes[0]}')";
    $result = $db->query($sql);
    $row = $result->fetch_row();
    $penyakit_terdeteksi = $row[0];
    $derajat_kepercayaan = round($densitas_baru[$codes[0]] * 100, 2);

    echo "<h2>Terdeteksi penyakit <b>{$penyakit_terdeteksi}</b> dengan derajat kepercayaan {$derajat_kepercayaan}%<h2>";
    echo "</div>";
  }
}
?>
<a href="diagnosa.php"> Diagnosa Lagi </a>