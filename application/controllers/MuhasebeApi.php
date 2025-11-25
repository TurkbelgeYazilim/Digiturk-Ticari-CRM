<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MuhasebeApi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        
        // JSON header
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');
    }

    public function index() {
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            
            switch ($action) {
                case 'getTahsilatOzeti':
                    echo json_encode($this->getTahsilatOzeti());
                    break;
                    
                case 'getPersonelTahsilatOzeti':
                    echo json_encode($this->getPersonelTahsilatOzeti());
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'error' => 'Invalid action']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Only POST method allowed']);
        }
    }

    private function getTahsilatOzeti() {
        $query = "SELECT 
            CASE m.tahsilat_tipi
                WHEN 1 THEN 'Banka'
                WHEN 2 THEN 'Çek'
                WHEN 3 THEN 'Kasa'
                WHEN 4 THEN 'Senet'
            END AS tahsilat_tipi,

            CASE m.durum
                WHEN 1 THEN 'Vadesi Geçen Tahsilat'
                WHEN 2 THEN 'Tahsilat Yapıldı'
            END AS durum,

            COUNT(m.id) AS toplam_adet,

            SUM(
                CASE 
                    WHEN m.tahsilat_tipi = 1 THEN COALESCE(bh.bh_giris,0)
                    WHEN m.tahsilat_tipi = 2 THEN COALESCE(c.cek_tutar,0)
                    WHEN m.tahsilat_tipi = 3 THEN COALESCE(kh.kh_giris,0)
                    WHEN m.tahsilat_tipi = 4 THEN COALESCE(s.senet_tutar,0)
                    ELSE 0
                END
            ) AS toplam_tutar

        FROM muhasebe_tahsilat_durum m
        LEFT JOIN bankahareketleri bh ON m.tahsilat_tipi = 1 AND m.kayit_id = bh.bh_id
        LEFT JOIN cek c ON m.tahsilat_tipi = 2 AND m.kayit_id = c.cek_id
        LEFT JOIN kasahareketleri kh ON m.tahsilat_tipi = 3 AND m.kayit_id = kh.kh_id
        LEFT JOIN senet s ON m.tahsilat_tipi = 4 AND m.kayit_id = s.senet_id

        WHERE
            m.durum IN (1, 2)

        GROUP BY m.tahsilat_tipi, m.durum
        ORDER BY m.tahsilat_tipi, m.durum";

        $result = $this->db->query($query);

        if ($result) {
            $rows = $result->result_array();
            return ['success' => true, 'rows' => $rows];
        } else {
            return ['success' => false, 'error' => 'Query failed: ' . $this->db->error()['message']];
        }
    }

    private function getPersonelTahsilatOzeti() {
        $query = "WITH hareket AS (
            SELECT
                m.id,
                m.tahsilat_tipi,
                m.durum,

                -- Tutarı, tahsilat tipine göre ilgili kaynaktan al
                CASE 
                    WHEN m.tahsilat_tipi = 1 THEN bh.bh_giris
                    WHEN m.tahsilat_tipi = 2 THEN c.cek_tutar
                    WHEN m.tahsilat_tipi = 3 THEN kh.kh_giris
                    WHEN m.tahsilat_tipi = 4 THEN s.senet_tutar
                    ELSE 0
                END AS tutar,

                -- Bu tahsilatın ilişkili cari kaydını oluşturan kullanıcı
                CASE 
                    WHEN m.tahsilat_tipi = 1 THEN cbh.cari_olusturan
                    WHEN m.tahsilat_tipi = 2 THEN cc.cari_olusturan
                    WHEN m.tahsilat_tipi = 3 THEN ckh.cari_olusturan
                    WHEN m.tahsilat_tipi = 4 THEN cs.cari_olusturan
                END AS cari_olusturan_id

            FROM muhasebe_tahsilat_durum m
            LEFT JOIN bankahareketleri   bh  ON m.tahsilat_tipi = 1 AND m.kayit_id = bh.bh_id
            LEFT JOIN cari               cbh ON bh.bh_cariID = cbh.cari_id

            LEFT JOIN cek                c   ON m.tahsilat_tipi = 2 AND m.kayit_id = c.cek_id
            LEFT JOIN cari               cc  ON c.cek_cariID = cc.cari_id

            LEFT JOIN kasahareketleri    kh  ON m.tahsilat_tipi = 3 AND m.kayit_id = kh.kh_id
            LEFT JOIN cari               ckh ON kh.kh_cariID = ckh.cari_id

            LEFT JOIN senet              s   ON m.tahsilat_tipi = 4 AND m.kayit_id = s.senet_id
            LEFT JOIN cari               cs  ON s.senet_cariID = cs.cari_id

            WHERE
                m.durum = 1  -- Sadece 'Vadesi Geçen Tahsilat'
        )

        SELECT
            COALESCE(CONCAT(k.kullanici_ad, ' ', k.kullanici_soyad), '—') AS kullanici,
            COUNT(h.id)                                                   AS toplam_adet,
            SUM(h.tutar)                                                  AS toplam_tutar
        FROM hareket h
        LEFT JOIN kullanicilar k ON k.kullanici_id = h.cari_olusturan_id
        GROUP BY h.cari_olusturan_id, k.kullanici_ad, k.kullanici_soyad
        ORDER BY SUM(h.tutar) DESC
        LIMIT 3";

        $result = $this->db->query($query);

        if ($result) {
            $rows = $result->result_array();
            return ['success' => true, 'rows' => $rows];
        } else {
            return ['success' => false, 'error' => 'Query failed: ' . $this->db->error()['message']];
        }
    }
}
?>