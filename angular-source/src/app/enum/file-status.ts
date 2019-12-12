export class FileStatus {
	static New: string = 'new';
	static Queued: string = 'queued';
	static Waiting: string = 'waiting';
	static Downloading: string = 'downloading';
	static Downloaded: string = 'downloaded';

	public static getData() {
		var data = {};

		data[FileStatus.New] = 'New';
		data[FileStatus.Queued] = 'Added to queue';
		data[FileStatus.Waiting] = 'In progress';
		data[FileStatus.Downloading] = 'Downloading';
		data[FileStatus.Downloaded] = 'Downloaded';

		return data;
	}

	public static getStatusLabel(status: string) {
		switch (status) {
			case FileStatus.New:
				return 'label-default';

			case FileStatus.Queued:
				return 'label-success';

			case FileStatus.Waiting:
			case FileStatus.Downloading:
				return 'label-info';

			case FileStatus.Downloaded:
				return 'label-primary';

			default:
				return 'label-default';
		}
	}
}