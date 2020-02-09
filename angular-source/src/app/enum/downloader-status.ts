export class DownloaderStatus {
	static Idle: string = 'idle';
	static Breaking: string = 'breaking_operation';
	static Continuation: string = 'continuation';
	static Downloading: string = 'downloading';
	static WaitingForResponse: string = 'waiting_for_response'
}