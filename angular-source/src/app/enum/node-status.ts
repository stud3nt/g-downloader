export class NodeStatus {
	public static Waiting: string = 'waiting'; // waiting/in action
	public static Blocked: string = 'blocked'; // banned/blocked;
	public static Favorited: string = 'favorited'; // favorited (displayed on top of list);
	public static Finished: string = 'finished'; // finished (all interesting images downloaded)
	public static Queued: string = 'queued'; // added to download queue;
	public static Saved: string = 'saved'; // saved in database;
	public static Downloaded: string = 'downloaded'; // downloaded;
	public static NewContent: string = 'new_content'; // new content available

	public static buttonStatusClass(status: string): string {
		switch (status) {
			case NodeStatus.Waiting:
				return 'btn-default';

			case NodeStatus.Blocked:
				return 'btn-danger';

			case NodeStatus.Downloaded:
			case NodeStatus.NewContent:
			case NodeStatus.Finished:
				return 'btn-success';

			case NodeStatus.Queued:
			case NodeStatus.Favorited:
			case NodeStatus.Saved:
				return 'btn-primary';

		}

		return 'btn-default';
	}
}