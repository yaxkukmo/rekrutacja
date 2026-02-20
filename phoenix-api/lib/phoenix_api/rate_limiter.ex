defmodule PhoenixApi.RateLimiter do
  use GenServer

  @user_limit 5
  @user_window_seconds 600
  @global_limit 1000
  @global_window_seconds 3600

  # Client API

  def start_link(_opts) do
    GenServer.start_link(__MODULE__, %{}, name: __MODULE__)
  end

  @spec check_and_increment(integer()) :: :allow | :deny_user | :deny_global
  def check_and_increment(user_id) do
    GenServer.call(__MODULE__, {:check_and_increment, user_id})
  end

  # Server callbacks

  @impl true
  def init(_state) do
    {:ok, %{}}
  end

  @impl true
  def handle_call({:check_and_increment, user_id}, _from, state) do
    now = DateTime.utc_now()

    {user_timestamps, state} = get_and_clean(state, {:user, user_id}, now, @user_window_seconds)
    {global_timestamps, state} = get_and_clean(state, :global, now, @global_window_seconds)

    cond do
      length(user_timestamps) >= @user_limit ->
        {:reply, :deny_user, state}

      length(global_timestamps) >= @global_limit ->
        {:reply, :deny_global, state}

      true ->
        state =
          state
          |> Map.put({:user, user_id}, [now | user_timestamps])
          |> Map.put(:global, [now | global_timestamps])

        {:reply, :allow, state}
    end
  end

  # Helpers

  defp get_and_clean(state, key, now, window_seconds) do
    timestamps =
      state
      |> Map.get(key, [])
      |> Enum.filter(fn ts -> DateTime.diff(now, ts, :second) < window_seconds end)

    {timestamps, Map.put(state, key, timestamps)}
  end
end
