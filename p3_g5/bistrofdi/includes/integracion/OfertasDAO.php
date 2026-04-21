<?php
/**
 * Clase para el acceso a datos de las ofertas.
 */

class OfertaDAO {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Lista todas las ofertas (actuales y pasadas).
     */
    public function listarOfertas() {
        $sql = "SELECT id, nombre, descripcion, fecha_inicio, fecha_fin, descuento_porcentaje 
                FROM ofertas ORDER BY fecha_inicio DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();
        $ofertas = [];

        while ($row = $result->fetch_assoc()) {
            $oferta = new OfertaDTO();
            $oferta->setId($row['id']);
            $oferta->setNombre($row['nombre']);
            $oferta->setDescripcion($row['descripcion']);
            $oferta->setFechaInicio($row['fecha_inicio']);
            $oferta->setFechaFin($row['fecha_fin']);
            $oferta->setDescuentoPorcentaje($row['descuento_porcentaje']);
            $ofertas[] = $oferta;
        }

        $result->free();
        $stmt->close();

        return $ofertas;
    }

    /**
     * Crea una nueva oferta y sus productos asociados.
     */
    public function crearOferta(OfertaDTO $oferta) {
        $this->db->begin_transaction();

        try {
            $sql = "INSERT INTO ofertas (nombre, descripcion, fecha_inicio, fecha_fin, descuento_porcentaje) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $nombre = $oferta->getNombre();
            $desc = $oferta->getDescripcion();
            $inicio = $oferta->getFechaInicio();
            $fin = $oferta->getFechaFin();
            $descPerc = $oferta->getDescuentoPorcentaje();

            $stmt->bind_param("ssssd", $nombre, $desc, $inicio, $fin, $descPerc);
            $stmt->execute();
            $idOferta = $this->db->insert_id;
            $stmt->close();

            $sqlProd = "INSERT INTO ofertas_productos (oferta_id, producto_id, cantidad) VALUES (?, ?, ?)";
            $stmtProd = $this->db->prepare($sqlProd);

            foreach ($oferta->getProductos() as $p) {
                $idProd = $p['producto_id'];
                $cant = $p['cantidad'];
                $stmtProd->bind_param("iii", $idOferta, $idProd, $cant);
                $stmtProd->execute();
            }
            $stmtProd->close();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Obtiene los productos y cantidades de una oferta específica.
     */
    public function obtenerProductosDeOferta($idOferta) {
        $sql = "SELECT op.producto_id, op.cantidad, p.nombre, p.precio_base, p.iva 
                FROM ofertas_productos op
                INNER JOIN productos p ON op.producto_id = p.id
                WHERE op.oferta_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idOferta);
        $stmt->execute();
        $result = $stmt->get_result();
        $productos = [];

        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        $result->free();
        $stmt->close();
        return $productos;
    }

    /**
     * Borra una oferta
     */
    public function borrarOferta($id) {
        $sql = "DELETE FROM ofertas WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $exito = $stmt->execute();
        $stmt->close();
        return $exito;
    }

    /**
     * Obtiene ofertas activas según la fecha actual.
     */
    public function obtenerOfertasActivas() {
        $hoy = date('Y-m-d');
        $sql = "SELECT id, nombre, descripcion, fecha_inicio, fecha_fin, descuento_porcentaje 
                FROM ofertas 
                WHERE fecha_inicio <= ? AND fecha_fin >= ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $hoy, $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        $ofertas = [];

        while ($row = $result->fetch_assoc()) {
            $oferta = new OfertaDTO();
            $oferta->setId($row['id']);
            $oferta->setNombre($row['nombre']);
            $oferta->setDescripcion($row['descripcion']);
            $oferta->setFechaInicio($row['fecha_inicio']);
            $oferta->setFechaFin($row['fecha_fin']);
            $oferta->setDescuentoPorcentaje($row['descuento_porcentaje']);
            $ofertas[] = $oferta;
        }

        $result->free();
        $stmt->close();
        return $ofertas;
    }
}
?>
