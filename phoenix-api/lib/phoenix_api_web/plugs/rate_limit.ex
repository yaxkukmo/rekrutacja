defmodule PhoenixApiWeb.Plugs.RateLimit do
  import Plug.Conn
  import Phoenix.Controller

  alias PhoenixApi.RateLimiter

  def init(opts), do: opts

  def call(conn, _opts) do
    user = conn.assigns[:current_user]

    case RateLimiter.check_and_increment(user.id) do
      :allow ->
        conn

      :deny_user ->
        conn
        |> put_status(:too_many_requests)
        |> json(%{error: "Rate limit exceeded: maximum 5 imports per 10 minutes per user"})
        |> halt()

      :deny_global ->
        conn
        |> put_status(:too_many_requests)
        |> json(%{error: "Rate limit exceeded: maximum 1000 imports per hour"})
        |> halt()
    end
  end
end
