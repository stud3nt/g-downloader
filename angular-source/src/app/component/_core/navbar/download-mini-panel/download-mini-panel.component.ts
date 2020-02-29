import { Component, OnInit } from '@angular/core';
import { DownloaderStatus } from "../../../../enum/downloader-status";
import { DownloaderService } from "../../../../service/downloader.service";
import { JsonResponse } from "../../../../model/json-response";
import { RouterService } from "../../../../service/router.service";
import { FileType } from "../../../../enum/file-type";
import { WebSocketService } from "../../../../service/web-socket.service";
import { WebsocketOperation } from "../../../../enum/websocket-operation";
import { AuthService } from "../../../../service/auth.service";
import { DownloadStatus } from "../../../../model/download-status";
import { ToastrDataService } from "../../../../service/data/toastr-data.service";
import { ParsedFile } from "../../../../model/parsed-file";

@Component({
	selector: 'app-download-mini-panel',
	templateUrl: './download-mini-panel.component.html'
})
export class DownloadMiniPanelComponent implements OnInit {

	public mainButtonClass: string = 'navbar-main-button inactive';
	public miniPanelClass: string = 'dropdown-menu';

	public dropdownVisible: boolean = false;
	public dropdownFilesQueue: ParsedFile[] = [];

	public downloader: DownloadStatus = (new DownloadStatus());

	public DownloaderStatus = DownloaderStatus;
	public FileType = FileType;

	public _downloaderStatus: string = DownloaderStatus.Idle;

	private _websocketName: string = 'download_session';
	private _websocketDelay: number = 1500;

	constructor(
		private auth: AuthService,
		protected downloaderService: DownloaderService,
		protected toastrService: ToastrDataService,
		protected websocketService: WebSocketService,
		public routerService: RouterService
	) { }

	ngOnInit() {
		this.websocketService.createListener(
			this._websocketName, (response: JsonResponse) => {
				if (typeof response.data === 'object') {
					this.downloader = new DownloadStatus(response.data);

					if (this.downloader.queuedFiles.length > 0)
						this.dropdownFilesQueue = this.downloader.queuedFiles;

					setTimeout(() => {
						this.sendStatusRequest();
					}, this._websocketDelay);
				}
			}, (error) => {
				this.toastrService.addError('PARSER ERROR', error.message);
				console.log(error);
			}, () => {
				console.log('COMPLETE');
			}
		);

		this.sendStatusRequest();
	}

	protected sendStatusRequest(): void {
		this.websocketService.sendRequest(
			this._websocketName,
			WebsocketOperation.DownloadListStatus,
			this.auth.user.apiToken
		);
	}

	/**
	 * Starts downloading files process
	 */
	public start(): void {
		switch (this._downloaderStatus) {
			case DownloaderStatus.Downloading:
				this._websocketDelay = 1500;
				return;

			case DownloaderStatus.Breaking:
			case DownloaderStatus.WaitingForResponse:
				this._downloaderStatus = DownloaderStatus.Idle;
				this._websocketDelay = 1500;
				return;

			case DownloaderStatus.Idle:
			case DownloaderStatus.Continuation:
				this._downloaderStatus = DownloaderStatus.Downloading;
				this._websocketDelay = 250;
		}

		this.downloaderService.startDownloadProcess().subscribe((response: JsonResponse) => {
			if (this._downloaderStatus === DownloaderStatus.Downloading && response.data !== null && response.data.filesCount > 0) {
				this._downloaderStatus = DownloaderStatus.Continuation;
				this.start();
			} else {
				this.stop();
			}
		});
	}

	/**
	 * Stops downloading files process
	 */
	public stop(): void {
		this._downloaderStatus = DownloaderStatus.Breaking;
		this.downloaderService.stopDownloadProcess().subscribe((response) => {
			this._websocketDelay = 1500;
			this._downloaderStatus = DownloaderStatus.Idle;
			this.dropdownFilesQueue = [];
		});
	}

	public toggleMiniPanel(): void {
		this.dropdownVisible = !this.dropdownVisible;
		this.determineClasses();
	}

	protected determineClasses(): void {
		switch (this.downloader.status) {
			case DownloaderStatus.Idle:
				this.mainButtonClass = 'navbar-main-button inactive';
				break;

			case DownloaderStatus.Downloading:
				this.mainButtonClass = 'navbar-main-button active';
				break;

			case DownloaderStatus.WaitingForResponse:
				this.mainButtonClass = 'navbar-main-button waiting';
				break;
		}

		this.miniPanelClass = 'dropdown-menu' + ((this.dropdownVisible) ? ' visible' : ' hidden');
	}

}
