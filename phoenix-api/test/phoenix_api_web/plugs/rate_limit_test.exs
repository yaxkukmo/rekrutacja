defmodule PhoenixApiWeb.Plugs.RateLimitTest do
  use PhoenixApiWeb.ConnCase

  alias PhoenixApi.Repo
  alias PhoenixApi.Accounts.User

  setup do
    user =
      %User{}
      |> User.changeset(%{api_token: "rate_limit_test_token"})
      |> Repo.insert!()

    {:ok, user: user}
  end

  describe "GET /api/photos rate limiting" do
    test "allows first 5 requests", %{conn: conn} do
      for _ <- 1..5 do
        response =
          conn
          |> put_req_header("access-token", "rate_limit_test_token")
          |> get("/api/photos")

        assert response.status == 200
      end
    end

    test "returns 429 after 5 requests within 10 minutes", %{conn: conn} do
      for _ <- 1..5 do
        conn
        |> put_req_header("access-token", "rate_limit_test_token")
        |> get("/api/photos")
      end

      response =
        conn
        |> put_req_header("access-token", "rate_limit_test_token")
        |> get("/api/photos")

      assert response.status == 429
      assert json_response(response, 429)["error"] =~ "5 imports per 10 minutes"
    end

    test "rate limit is per user — other users not affected", %{conn: conn} do
      other_user =
        %User{}
        |> User.changeset(%{api_token: "other_rate_limit_token"})
        |> Repo.insert!()

      for _ <- 1..5 do
        conn
        |> put_req_header("access-token", "rate_limit_test_token")
        |> get("/api/photos")
      end

      response =
        conn
        |> put_req_header("access-token", other_user.api_token)
        |> get("/api/photos")

      assert response.status == 200
    end
  end
end
