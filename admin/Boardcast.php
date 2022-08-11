<?php
class Boardcast {
    public int $id;
    public string $link, $time, $type, $update, $title;
    public bool $stick;

    public static function of($id, $link, $time, $type, $update, $stick, $title): self
    {
        $bc = new self();
        $bc->id = $id;
        $bc->link = $link;
        $bc->time = $time;
        $bc->type = $type;
        $bc->update = $update;
        $bc->stick = $stick;
        $bc->title = $title;
        return $bc;
    }

    public static function from_array($arr): self
    {
        return Boardcast::of($arr['id'], $arr['link'], $arr['time'], $arr['type'], $arr['update'], $arr['stick'], $arr['title']);
    }

    public static function from_id($conn, $id): self
    {
        $stmt = $conn->prepare("SELECT * FROM boardcasts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return self::from_array(mysqli_fetch_array($stmt->get_result()));
    }

    public static function add_boardcast($conn, $link, $type, $stick, $title, &$cast): bool
    {
        $stmt = $conn->prepare("INSERT INTO boardcasts (type, link, time, stick, `update`, title) VALUES (?, ?, ?, ?, ?, ?)");
        $time = (new DateTime())->format('Y-m-d H:i:s');
        $stmt->bind_param("sssiss", $type, $link, $time, $stick, $time, $title);
        if (!$stmt->execute()) return false;
        $cast = new Boardcast();
        $sql = "SELECT LAST_INSERT_ID() AS id FROM boardcasts";
        $arr = mysqli_fetch_array(mysqli_query($conn, $sql));
        $cast = self::of($arr['id'], $link, $time, $type, $time, $stick, $title);
        return true;
    }

    public function update($conn): bool
    {
        $stmt = $conn->prepare("UPDATE boardcasts SET type = ?, link = ?, stick = ?, `update` = ?, title = ? WHERE id = ?");
        $this->update = (new DateTime())->format('Y-m-d H:i:s');
        $stmt->bind_param("ssissi", $this->type, $this->link, $this->stick, $this->update, $this->title, $this->id);
        return $stmt->execute();
    }

    public static function delete_boardcast($conn, $id): bool
    {
        $stmt = $conn->prepare("DELETE FROM boardcasts WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public static function get_boardcasts_json($conn, $page, $amount): array
    {
        $stmt = $conn->prepare("SELECT * FROM boardcasts ORDER BY stick DESC, `update` DESC LIMIT ?, ?");
        $top = $page * $amount;
        $stmt->bind_param("ii", $top, $amount);
        $stmt->execute();
        $result = $stmt->get_result();
        $list = array();
        while ($arr = mysqli_fetch_array($result)) {
            $list[] = (array)self::from_array($arr);
        }
        $sql = "SELECT COUNT(*) AS amount FROM boardcasts";
        $cnt = mysqli_fetch_array(mysqli_query($conn, $sql))['amount'];
        return array(
            'cnt' => $cnt,
            'list' => $list
        );
    }
}