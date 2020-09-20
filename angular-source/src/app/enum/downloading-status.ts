export enum DownloadingStatus {
	Idle = 'idle',
	Breaking = 'breaking_operation',
	Continuation = 'continuation',
	Downloading = 'downloading',
    RefreshingList = 'refreshing_list',
	WaitingForResponse = 'waiting_for_response'
}
