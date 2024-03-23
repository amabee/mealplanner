<?php

include ("connection.php");


class MealApi
{

    function login($json, $conn)
    {
        $json = json_decode($json, true);

        try {
            $sql = "SELECT * FROM users WHERE (username = :uname OR email = :uname) AND password = :password";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":uname", $json["username"], PDO::PARAM_STR);
            $stmt->bindParam(":password", $json["password"], PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                echo json_encode(array("error" => "Invalid Credentials"));
            } else {
                echo json_encode($result);
            }
        } catch (Exception $e) {
            echo json_encode(array("error" => $e->getMessage()));
        }
    }
    function signup($json, $conn)
    {
        $json = json_decode($json, true);

        try {
            $check_sql = "SELECT * FROM users WHERE username = :uname OR email = :email";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bindParam(":uname", $json["username"], PDO::PARAM_STR);
            $check_stmt->bindParam(":email", $json["email"], PDO::PARAM_STR);
            $check_stmt->execute();
            $existing_user = $check_stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing_user) {
                echo json_encode(array("error" => "Username or email already exists"));
                return;
            }

            $sql = "INSERT INTO users (username, email, password) VALUES (:uname, :email, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":uname", $json["username"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $json["email"], PDO::PARAM_STR);
            $stmt->bindParam(":password", $json["password"], PDO::PARAM_STR);
            $result = $stmt->execute();
            if ($result) {
                echo json_encode("User registered successfully");
            } else {
                echo json_encode(array("error" => "Failed to register user"));
            }
        } catch (Exception $e) {
            echo json_encode(array("error" => $e->getMessage()));
        }
    }

    function getRecipe($json, $conn)
    {
        $json = json_decode($json, true);

        try {
            $sql = "SELECT * FROM recipes WHERE (author_id = :author_id)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":author_id", $json["author_id"], PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$result) {
                echo json_encode(array("error" => "No Recipes found for this user"));
            } else {
                echo json_encode($result);
            }
        } catch (Exception $e) {
            echo json_encode(array("error" => $e->getMessage()));
        }
    }

    function addRecipe($json, $conn)
    {
        $json = json_decode($json, true);

        try {
            $sql = "INSERT INTO `recipes`(`title`, `description`, `instructions`, `preparation_time`, `cooking_time`, `servings`, `author_id`, `image`) ";
            $sql .= "VALUES (:title, :desc, :ins, :prep_time, :cook_time, :servings, :author_id, :image)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":title", $json["title"], PDO::PARAM_STR);
            $stmt->bindParam(":desc", $json["desc"], PDO::PARAM_STR);
            $stmt->bindParam(":ins", $json["ins"], PDO::PARAM_STR);
            $stmt->bindParam(":prep_time", $json["prep_time"], PDO::PARAM_STR);
            $stmt->bindParam(":cook_time", $json["cook_time"], PDO::PARAM_STR);
            $stmt->bindParam(":servings", $json["servings"], PDO::PARAM_STR);
            $stmt->bindParam(":author_id", $json["author_id"], PDO::PARAM_STR);

            $imagePath = "images/";
            $imageName = uniqid() . '.jpg';
            move_uploaded_file($_FILES['image']['tmp_name'], $imagePath . $imageName);
            $imageUrl = $imagePath . $imageName;

            $stmt->bindParam(":image", $imageUrl, PDO::PARAM_STR);

            $res = $stmt->execute();
            if ($res) {
                echo json_encode("Recipe Created");
            } else {
                echo json_encode(array("error" => "Something went wrong while adding your recipe"));
            }
        } catch (PDOException $e) {
            echo json_encode(array("error" => $e->getMessage()));
        }
    }



    function addIngredients($json, $conn)
    {
        $json = json_decode($json, true);

        try {
            $sql = "INSERT INTO `recipe_ingredients`(`recipe_id`, `ingredient_name`, `amount`) ";
            $sql .= "VALUES (:recipe_id, :ingredient_name, :amount)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":recipe_id", $json["rid"]);
            $stmt->bindParam("ingredient_name", $json["ing_name"], PDO::PARAM_STR);
            $stmt->bindParam(":amount", $json["amount"], PDO::PARAM_STR);

            $res = $stmt->execute();
            if ($res) {
                echo json_encode("Successfully Added the ingredients");
            } else {
                echo json_encode(array("error" => "Something went wrong while adding the ingredients"));
            }
        } catch (PDOException $e) {
            echo json_encode(array("error" => $e->getMessage()));
        }
    }

    function getIngredients($json, $conn)
    {
        $json = json_decode($json, true);

        try {
            $sql = "SELECT * FROM `recipe_ingredients` WHERE recipe_id = :recipe_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":recipe_id", $json["recipe_id"], PDO::PARAM_STR);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$res) {
                echo json_encode(array("error" => "No recipe found"));
            } else {
                echo json_encode($res);
            }
        } catch (PDOException $e) {
            echo json_encode(array("error" => $e->getMessage()));
        }
    }

    function addMealPlan($json, $conn)
    {
        $json = json_decode($json, true);

        try {
            $sql = "INSERT INTO `meal_plans`(`user_id`, `recipe_id`,  `start_date`, `end_date`) ";
            $sql .= "VALUES (:user_id, :recipe_id, :start_date, :end_date)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":user_id", $json["user_id"]);
            $stmt->bindParam(":recipe_id", $json["recipe_id"]);
            $stmt->bindParam(":start_date", $json["start_date"]);
            $stmt->bindParam(":end_date", $json["end_date"]);
            $res = $stmt->execute();
            if ($res) {
                echo json_encode("Meal Plan Created Successfully");
            } else {
                echo json_encode(array("error" => "Something went wrong while creating your meal plan"));
            }
        } catch (PDOException $e) {
            echo json_encode(array("error" => $e->getMessage()));
        }
    }

    function getMealPlan($json, $conn)
    {
        $json = json_decode($json, true);

        try {
            $sql = "SELECT *, recipes.*, users.user_id FROM `meal_plans`
            INNER JOIN recipes ON meal_plans.recipe_id = recipes.recipe_id
            INNER JOIN users ON recipes.author_id = users.user_id
            WHERE users.user_id = :user_id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":user_id", $json["user_id"]);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($res) {
                echo json_encode($res);
            } else {
                echo json_encode(array("error" => "No Meal Plans Yet"));
            }
        } catch (PDOException $e) {
            echo json_encode(array("error" => $e->getMessage()));
        }
    }

    function removeRecipe($json, $conn)
    {
        $json = json_decode($json, true);

        try {
            $sql = "DELETE FROM `recipes` WHERE recipe_id = :rid AND author_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":rid", $json["recipe_id"]);
            $stmt->bindParam("user_id", $json["user_id"]);
            if ($stmt->execute()) {
                echo json_encode("Successfully Removed Recipe");
            } else {
                echo json_encode(array("error" => $stmt->errorInfo()));
            }
        } catch (PDOException $e) {
            echo json_encode(array("error" => $e->getMessage()));
        }
    }

}
$api = new MealApi();

if ($_SERVER["REQUEST_METHOD"] == "GET" || $_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset ($_REQUEST['operation']) && isset ($_REQUEST['json'])) {
        $operation = $_REQUEST['operation'];
        $json = $_REQUEST['json'];

        switch ($operation) {
            case 'login':
                echo $api->login($json, $conn);
                break;
            case 'signup':
                echo $api->signup($json, $conn);
                break;
            case 'myrecipes':
                echo $api->getRecipe($json, $conn);
                break;
            case 'addrecipe':
                echo $api->addRecipe($json, $conn);
                break;
            case 'addingredients':
                echo $api->addIngredients($json, $conn);
                break;
            case 'getingredients':
                echo $api->getIngredients($json, $conn);
                break;
            case 'addmealplan':
                echo $api->addMealPlan($json, $conn);
                break;
            case 'getmealplan':
                echo $api->getMealPlan($json, $conn);
                break;
            case 'removerecipe':
                echo $api->removeRecipe($json, $conn);
                break;
            default:
                echo json_encode(["error" => "Invalid operation"]);
                break;
        }
    } else {
        echo json_encode(["error" => "Missing parameters"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>